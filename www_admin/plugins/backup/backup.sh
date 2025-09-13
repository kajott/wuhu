#!/bin/bash

if [ -n "$1" -a -b "$1" ] ; then
    DEVICE="$1"
    OUTDIR="$2"
else
    DEVICE=""
    OUTDIR="$1"
fi

if [ -z "$OUTDIR" -o "$OUTDIR" == "-h" ] ; then
    echo "Invalid parameters: $*"
    echo "Usage: $0 [<DEVICE>] <OUTDIR>"
    echo "If DEVICE is specified, it will be mounted and unmounted by the script"
    echo "and OUTDIR is a directory to be created on the device's filesystem."
    echo "Otherwise, OUTDIR is the full path to the target directory."
    exit 2
fi

while [ ! -d www_party ] ; do
    if [ "$(pwd)" == "/" ] ; then
        echo "FATAL: Wuhu installation directory not found, aborting."
        exit 1
    fi
    cd ..
done

echo "========== CREATING WUHU BACKUP =========="
echo
echo "Start Date/Time:  $(date)"
echo "Source Directory: $(pwd)"
[ -n "$DEVICE" ] && echo "Target Device:    $DEVICE"
echo "Target Directory: $OUTDIR"
echo
code=0

if [ -n "$DEVICE" ] ; then
    echo "Mounting the device:"
    res="$( set -x ; udisksctl mount -b $DEVICE 2>&1 )"
    ret=$?
    echo $res
    if [ "$ret" == "0" ] ; then
        MOUNT="$(echo $res | sed 's/.* //')"
        echo "[OK - mount point: $MOUNT]"
        echo
    elif echo "$res" | grep AlreadyMounted >/dev/null ; then
        MOUNT="$(echo "$res" | tr '\n' '|' | cut -d'`' -f2 | cut -d"'" -f1)"
        echo "[OK - already mounted: $MOUNT]"
        echo
    else
        echo "[FAILED - aborting backup]"
        exit 1
    fi
fi

if [ "$code" == "0" -a -n "$DEVICE" -a ! -d "$MOUNT" ] ; then
    echo "FATAL: mount point '$MOUNT' is not a valid directory, aborting."
    echo
    code=1
fi

if [ "$code" == "0" -a -n "$DEVICE" ] ; then
    OUTDIR="$MOUNT/$OUTDIR"
    echo "Full path to target directory: $OUTDIR"
    echo
fi

if [ "$code" == "0" -a ! -d "$OUTDIR" ] ; then
    echo "Target directory doesn't exist, creating it:"
    if ( set -x ; mkdir "$OUTDIR" ) ; then
        echo "[OK]"
    else
        echo "[FAILED]"
        code=1
    fi
    echo
fi

if [ "$code" == "0" ] ; then
    echo "Backing up data ..."
    subdirs=""
    for subdir in www_party www_admin screenshots entries_private entries_public ; do
        [ -d "$subdir" ] && subdirs="$subdirs $subdir"
    done
    # rsync options used here:
    # -r = recursive
    # -L = copy symlinks as new files (*not* as symlinks, because the backup
    #      medium may be formatted in FAT32 or exFAT, which don't support symlinks)
    # -t = preserve modification times
    # -x = don't cross filesystems (shouldn't happen anyway)
    # -v = verbose = show filenames
    # --delete = delete files not present in the source ("mirror mode")
    # absent: -p = copy permissions (FAT32/exFAT/NTFS don't support all of them)
    # absent: -a = shorthand for -rlptgoD (would try to copy user/group IDs)
    ( set -x ; rsync -rLtxv --delete $subdirs "$OUTDIR/" )

    files=""
    for file in ssl_cert.crt ssl_cert.key wuhuproxy_key ; do
        [ -r "$file" ] && files="$files $file"
    done
    if [ "$code" == "0" -a -n "$files" ] ; then
        ( set -x ; cp -v $files "$OUTDIR/" )
        code=$?
    fi
    echo
fi

if [ "$code" == "0" ] ; then
    echo "Creating database backup ..."
    SQL_USERNAME="$(grep SQL_USERNAME www_party/database.inc.php | cut -d, -f2 | tr "'" '"' | cut -d'"' -f2)"
    SQL_PASSWORD="$(grep SQL_PASSWORD www_party/database.inc.php | cut -d, -f2 | tr "'" '"' | cut -d'"' -f2)"
    SQL_DATABASE="$(grep SQL_DATABASE www_party/database.inc.php | cut -d, -f2 | tr "'" '"' | cut -d'"' -f2)"
    if [ -z "$SQL_USERNAME" -o -z "$SQL_PASSWORD" -o -z "$SQL_DATABASE" ] ; then
        echo "[FAILED - could not determine databse credentials]"
        code=1
    else
        sqlfile="$OUTDIR/db.sql"
        echo "${PS4}mysqldump -u $SQL_USERNAME -p $SQL_DATABASE > $sqlfile"
        if mysqldump -u "$SQL_USERNAME" "-p$SQL_PASSWORD" "$SQL_DATABASE" > "$sqlfile" ; then
            echo "[OK]"
        else
            echo "[FAILED]"
            code=1
        fi
    fi
    echo
fi

if [ -n "$DEVICE" ] ; then
    echo "Unmounting the device:"
    if ( set -x ; udisksctl unmount -b $DEVICE ) ; then
        echo "[OK]"
    else
        echo "[FAILED]"
    fi
fi

echo
echo "========== WUHU BACKUP DONE =========="
echo "End Date/Time: $(date)"
if [ "$code" == "0" ] ; then
    echo "Status:        SUCCESS"
else
    echo "Status:        FAILED"
fi
echo "Exit Code:     $code"
exit $code
