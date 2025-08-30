<?php require("../../bootstrap.inc.php");

$compos = get_compos();
$entries = SQLLib::selectRows("select * from compoentries");
$data = array();

foreach ($entries as $e) {
    $item = array();
    foreach ($e as $k => $v) {
        $item[$k] = $v;
    }

    $c = $compos[$e->compoid];
    $item["componame"] = $c->name;
    $item["compostart"] = $c->start;
    $item["compodir"] = $c->dirname;

    $fn = get_compoentry_file_path($e);
    $item["filepath"] = $fn;
    $mtime = @filemtime($fn);
    if ($mtime) { $item["filemtime"] = $mtime; }

    $data[] = $item;
}

@header("Content-Type: text");
echo json_encode($data);

?>
