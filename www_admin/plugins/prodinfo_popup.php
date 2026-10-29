<?php
/*
Plugin name: Entry info popup
Description: Show a popup window with full entry information when clicking a screenshot during voting.
*/
if (!defined("ADMIN_DIR")) exit();

function prodinfo_popup_endform()
{ ?>
  <script>
    (function(){
      for (const a of document.querySelectorAll(".screenshot a")) {
        a.addEventListener("click", function(ev) {
          var el = ev.target;
          while (!el.href) { el = el.parentNode; }
          var req = new XMLHttpRequest();
          req.addEventListener("load", function(ev) {
            var dialog = document.getElementById("prodinfo");
            dialog.innerHTML = req.responseText + '<p class="close_row"><button type="button" commandfor="prodinfo" command="close">Close</button></p>';
            dialog.showModal();
          });
          req.open("GET", "/prodinfo.php?id=" + URL.parse(el.href).searchParams.get("id"));
          req.send();
          ev.preventDefault();
        });
      }
    })();
  </script>
  <dialog id="prodinfo" onmousedown="event.target==this && this.close()"></dialog>
<?php
}

add_hook("vote_endform","prodinfo_popup_endform");

?>
