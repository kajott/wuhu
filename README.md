# Wuhu (CompoKit Edition)
Lightweight party management system - http://wuhu.function.hu

> [!NOTE]
> This is **not** the official upstream version of Wuhu,
> but a custom fork created by the organizers of the Deadline and Dream210 demoparties.
> [See below](#CompoKit_Edition_Extensions) for a detailed list of changes.

## Requirements

### Server side:
* Apache 2.x (Not tested with other servers but it should probably work fine)
* PHP 5.x or later (tested with 8.2 and 8.4)
* MySQL 5.x (works fine with MariaDB)
### Beamer side: 
* HTML5 compatible browser (Chrome/Firefox preferred)
* Machine to handle it (any OS)

## Basic installation
Within [CompoKit](https://github.com/kajott/CompoKit), there's a nice
[installation script](https://github.com/kajott/CompoKit/tree/master/src/wuhu-setup)
that takes care of setting up everything important (except Let's Encrypt)
on a fresh Debian 12 or 13 system, optionally including setup of a complex,
but useful proxy system for hybrid cloud / on-premise setups (inspired by
Charlie). On other platforms or distributions, you need to perform the
installation manually as explained below.

### Apache
1. Set up a basic Apache server with two virtual hosts, one for the users and one for the admins. One convenient way to configure this is
         http://party.lan pointing to /var/www/party
         http://admin.lan pointing to /var/www/admin
       The admin one is recommended to have SSL configured.         
       It's important to set up a working nameserver too!
    
2. Set AllowOverride in your Apache configs to All.

### MySQL 
Set up a MySQL server, create a database, and create an account that has full read/write access to the database.
       
### Miscellaneous Unix stuff
1. Create a directory where you will store your compo entries. This dir has to be readable and writeable by Apache, and for convenience, it's useful if it's the root dir of a password protected FTP.  
2. Create another directory, where you will store the screenshots. This dir has to be readable and writeable by Apache, but it will only serve as storage, it doesn't have to be accessible by anything else.
3. Unpack the www_admin dir into your admin dir and unpack the www_party dir into your party dir.

### Deployment
1. Open your admin interface in a web browser. It should bring you to the deployment form.
2. Fill the form accordingly, and remember to use absolute paths everywhere.
3. On success, you should be forwarded to the admin interface. Note that if you set a user/pass for the interface, you will be prompted for it.
       
## Using the beam system
1. Click the "Slideviewer" link in the admin
2. Enter the original slide resolution in which the design was done
3. Press "Open viewer" - most browsers allow you to switch to fullscreen with F11.
  
Both beam systems rely on simple keypresses for operation.
  
* ALT-F4 - quit
* LEFT ARROW - previous slide / minus one minute in countdown mode
* RIGHT ARROW - next slide / plus one minute in countdown mode
* HOME - first slide
* END - last slide
* S - partyslide rotation mode
* T - reload stylesheet (without changing the slide contents) 
* SPACE - re-read beamer.data (and quit partyslide mode)
    
This last key essentially means that once you've used the "BEAMER" menu on the admin interface, you must press SPACE to refresh the data inside (and/or switch to another mode).

## CompoKit Edition Extensions

The following features are available in this Wuhu fork that are not (yet?)
present in upstream Wuhu:
* introduction of a platform/options field, optional per compo,
  and with different preset options per compo
* screenshots can be optional per compo
* PartyMeister-style callbacks from the slide viewer that can automatically
  unlock live voting of entries during a compo, and even normal voting
  after the end of a compo (all optional)
* on the visitor site, screenshots are cached
* visitors can download releases during voting
* there can be multiple rotation slide playlists
* the rotation slide timeout can be adjusted
* the slide viewer starts in rotation slide mode, not in compo mode
* individual or all compos can be omitted from the timetable (which is useful
  if you only want compo blocks, not single compos, on the timetable)
* added additional timetable event types
* the compo display order can be configured using a setting variable
  (e.g. set `compo_order` to `id` to have all compos listed in creation order)
* semi-automatic compo progression during prizegiving: the following compo
  (in the configured compo order) is preselected in the drop-down field
* the Lorem Ipsum (dummy data generator) plugin can also generate dummy votes
* new plugin that shows visitors a warning when their entry comment text
  becomes too long and risks being truncated on the beamslide
* new plugin that allows editing of various template and CSS files directly
  from the admin UI; no SFTP access needed
* the form element labels in the entry upload and edit fields can be customized
* minor admin UI refresh with proper dark theming

## Credits
Wuhu was created and is maintained by Gargaj / Conspiracy.

Additional effort by:
* Zoom / Conspiracy with the original admin design and QA
* Quarryman / Ogdoad for minor fixes
* lug00ber / Kvasigen for additional QA
* The TG Creativia crew for their immense QA effort
* KeyJ / TRBL and kb / Farbrausch for the CompoKit Edition extensions

Acknowledgments for external stuff are available in the license file.
