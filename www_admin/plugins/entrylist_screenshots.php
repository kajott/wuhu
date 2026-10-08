<?php
/*
Plugin name: Entry List Screenshots
Description: Displays mouse-over screenshots in compo entry lists in the admin interface.
*/

function entrylist_screenshot_script() {
  global $settings;
  ?><style>
    #entrylistscreenshot {
      display: none;
      position: fixed;
      box-sizing: content-box;
      width: <?=$settings['screenshot_sizex']?>px;
      height: <?=$settings['screenshot_sizey']?>px;
      margin: 16px 0 0 8px;
    }
  </style><img id="entrylistscreenshot"><script>
  (function() {
    var table = document.getElementById("compoentrylist");
    if (!table) { table = document.getElementById("entrylist"); }
    if (!table) { return; }
    for (var row of table.getElementsByTagName("tr")) {
      if (!row.classList.contains("entry")) { continue; }
      var id = 0;
      for (var cell of row.children) {
        var t = cell.innerText;
        if (t.substr(0,1) == "#") { id = parseInt(t.substr(1)); break; }
      }
      if (!id) { continue; }
      row.dataset.entryId = id;
      row.addEventListener('mouseover', function(ev) {
        var elem = ev.target;
        while (elem.dataset.entryId == undefined) { elem = elem.parentNode; }
        var img = document.getElementById("entrylistscreenshot");
        img.src = "screenshot.php?id= " + elem.dataset.entryId + "&show=thumb";
        img.style.display = "block";
      });
      row.addEventListener('mouseout', function() {
        document.getElementById("entrylistscreenshot").style.display = "none";
      });
    }
    document.addEventListener('mousemove', function(ev) {
      var img = document.getElementById("entrylistscreenshot");
      img.style.left = ev.clientX + "px";
      img.style.top  = ev.clientY + "px";
    });
  })();</script><?php
}

add_hook("admin_compo_entrylist_end", "entrylist_screenshot_script");
add_hook("admin_entrylist_end",       "entrylist_screenshot_script");
?>
