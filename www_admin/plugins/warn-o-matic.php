<?php
/*
Plugin name: Warn-O-Matic
Description: Warns the user when they type in too long entry description text that might be truncated on the slide.
*/


function warnomatic_get_config() {
  $cpl  = get_setting("warnomatic_chars_per_line");
  $warn = get_setting("warnomatic_warn_lines");
  $max  = get_setting("warnomatic_max_lines");
  return array(
    "cpl"  => $cpl  ? intval($cpl)  : 50,
    "warn" => $warn ? intval($warn) : 10,
    "max"  => $max  ? intval($max)  : 12,
  );
}

function warnomatic_activation() {
  $cfg = warnomatic_get_config();
  update_setting("warnomatic_chars_per_line", $cfg['cpl']);
  update_setting("warnomatic_warn_lines",     $cfg['warn']);
  update_setting("warnomatic_max_lines",      $cfg['max']);
}

function warnomatic_put_script() {
  $cfg = warnomatic_get_config();
  ?><script>
    function WarnOMatic(el, charsPerLine, warnLines, maxLines) {
      el.addEventListener('input', function(ev) {
        const lines = el.value.trim().split("\n").map((line) => Math.ceil((line.trim().length + 1) / charsPerLine)).reduce((a,b) => a+b, 0);
        if (lines >= warnLines) {
          var next = el.nextSibling;
          if (!next || !next.classList || !next.classList.contains("truncation-warning")) {
            var warn = document.createElement("p");
            warn.classList.add("truncation-warning");
            var w = document.createElement("strong");
            w.appendChild(document.createTextNode("Warning:"));
            warn.appendChild(w);
            warn.appendChild(document.createTextNode(" long text will be truncated on the slide!"));
            el.after(warn);
          }
        }
        if      (lines >= maxLines)  { el.style.backgroundColor = "#fcc"; }
        else if (lines >= warnLines) { el.style.backgroundColor = "#ffc"; }
        else                         { el.style.backgroundColor = ""; }
      });
    }
    WarnOMatic(document.getElementsByName("comment")[0], <?=$cfg['cpl']?>, <?=$cfg['warn']?>, <?=$cfg['max']?>);
  </script><?php
}

add_activation_hook(__FILE__,   "warnomatic_activation");
add_hook("uploadentry_endform", "warnomatic_put_script");
add_hook("editentry_endform",   "warnomatic_put_script");

?>
