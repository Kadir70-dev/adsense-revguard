#!/bin/bash
# Runs inside the wordpress container. Real WordPress, WP-CLI and HTTP (curl to Apache on localhost).
# Writes PASS/FAIL lines; the summary line counts them. Usage: suite.sh <release-zip-name>
set -u
ZIP=/qa/dist/$1
SLUG=kadirslot-ad-placement
WP="php /usr/local/bin/wp-cli.phar --allow-root --path=/var/www/html"
LOG=/var/www/html/wp-content/debug.log
B=http://localhost
O=revguard_free_settings
PAGE="$B/wp-admin/options-general.php?page=$SLUG"
ok() { if [ "$2" = "1" ]; then echo "PASS $1"; else echo "FAIL $1 [${3:-}]"; fi; }
a() { $WP eval-file /qa/scripts/assert.php "$@" 2>&1; }
wpq() { $WP "$@" >/dev/null 2>&1; }
# PHP warnings/notices/deprecations/fatals from this plugin since the last call, then clear the log.
clean_log() {
  local hits; hits=$(grep -E "PHP (Warning|Notice|Deprecated|Fatal|Parse)" "$LOG" 2>/dev/null | grep -c "/plugins/$SLUG/")
  ok "no PHP warnings/notices/fatals from $SLUG ($1)" "$([ "$hits" = 0 ] && echo 1)" "$(grep "/plugins/$SLUG/" "$LOG" | head -3)"
  : > "$LOG"
}
WPV=$($WP core version)
unz() { php -r '$z=new ZipArchive; if ($z->open($argv[1]) !== true || !$z->extractTo($argv[2])) { exit(1); }' "$1" "$2"; }
ver_ge() { [ "$(printf '%s\n%s\n' "$2" "$1" | sort -V | head -1)" = "$2" ]; }
echo "# WordPress $WPV, PHP $(php -r 'echo PHP_VERSION;'), WP-CLI $($WP --version | cut -d' ' -f2)"

# --- install --------------------------------------------------------------------------------------------------------
ADMIN_PASS=$(head -c 18 /dev/urandom | base64 | tr -dc A-Za-z0-9); ED_PASS=$(head -c 18 /dev/urandom | base64 | tr -dc A-Za-z0-9)
$WP core install --url=$B --title="KadirSlot QA" --admin_user=qaadmin --admin_password="$ADMIN_PASS" --admin_email=qa@example.test --skip-email >/dev/null
$WP user create qaeditor ed@example.test --role=editor --user_pass="$ED_PASS" >/dev/null
$WP rewrite structure '/%postname%/' --hard >/dev/null 2>&1
# Bind-mounted cores (for example WordPress 6.0) have no .htaccess; WP-CLI cannot detect mod_rewrite to write it.
[ -f /var/www/html/.htaccess ] || printf '%s\n' '# BEGIN WordPress' '<IfModule mod_rewrite.c>' 'RewriteEngine On' 'RewriteBase /' 'RewriteRule ^index\.php$ - [L]' 'RewriteCond %{REQUEST_FILENAME} !-f' 'RewriteCond %{REQUEST_FILENAME} !-d' 'RewriteRule . /index.php [L]' '</IfModule>' '# END WordPress' > /var/www/html/.htaccess
mkdir -p /var/www/html/wp-content/mu-plugins && cp /qa/mu-plugins/*.php /var/www/html/wp-content/mu-plugins/
$WP post create --post_type=post --post_status=publish --post_title="QA Post" --post_name=qa-post --post_content=$'<p>Para one.</p>\n<p>Para two.</p>\n<p>Para three.</p>\n<p>Para four.</p>\n<p>[revguard_ad id="1"]</p>\n<p>[revguard_ad id="4"]</p>' >/dev/null
$WP post create --post_type=page --post_status=publish --post_title="QA Page" --post_name=qa-page --post_content=$'<p>Page one.</p>\n<p>Page two.</p>' >/dev/null
chown -R www-data:www-data /var/www/html/wp-content
: > "$LOG"

# --- baseline reproduction: v1.1.1 + RevGuard Free 1.1.0 (expected to fail; proves the root cause) ------------------
wpq plugin install /qa/fixtures/revguard-free-1.1.0.zip
wpq plugin install /qa/fixtures/kadirslot-ad-placement-1.1.1.zip
wpq plugin activate revguard-free
OUT=$($WP plugin activate $SLUG 2>&1)
ok "baseline 1.1.1 reproduces 'Cannot redeclare RevGuardFree\\pro_active()' with RevGuard Free active" "$(echo "$OUT" | grep -q 'Cannot redeclare.*RevGuardFree.\{1,2\}pro_active' && echo 1)" "$OUT"
$WP plugin deactivate --all >/dev/null 2>&1; rm -rf /var/www/html/wp-content/plugins/{revguard-free,$SLUG}; a reset >/dev/null; : > "$LOG"

# --- fresh activation / deactivation ---------------------------------------------------------------------------------
OUT=$($WP plugin install "$ZIP" --activate 2>&1); ok "install + activate release ZIP" "$(echo "$OUT" | grep -q "Plugin '$SLUG' activated" && echo 1)" "$OUT"
ok "installed version 1.1.2" "$([ "$($WP plugin get $SLUG --field=version)" = 1.1.2 ] && echo 1)"
a fresh
a set-good
wpq plugin deactivate $SLUG; ok "deactivate" "$([ "$($WP plugin get $SLUG --field=status)" = inactive ] && echo 1)"
a is-good
wpq plugin activate $SLUG; a is-good
clean_log "activation lifecycle"

# --- upgrade from RevGuard Free 1.0.1 (raw-code settings) -------------------------------------------------------------
wpq plugin deactivate $SLUG; a reset >/dev/null
wpq plugin install /qa/fixtures/revguard-free-1.0.1.zip --activate
a seed-legacy
OUT=$($WP plugin activate $SLUG 2>&1); ok "activate 1.1.2 while RevGuard Free 1.0.1 active: no fatal" "$(echo "$OUT" | grep -q 'activated' && ! echo "$OUT" | grep -qi 'fatal' && echo 1)" "$OUT"
a passive; a legacy-intact
wpq plugin deactivate revguard-free
$WP eval 'echo "";' >/dev/null 2>&1   # next request: KadirSlot boots and migrates
a migrated
clean_log "1.0.1 migration"
rm -rf /var/www/html/wp-content/plugins/revguard-free

# --- in-place upgrade KadirSlot 1.1.1 -> 1.1.2 ---------------------------------------------------------------------
a reset >/dev/null; a set-good
wpq plugin install /qa/fixtures/kadirslot-ad-placement-1.1.1.zip --force --activate
ok "1.1.1 installed" "$([ "$($WP plugin get $SLUG --field=version)" = 1.1.1 ] && echo 1)"
OUT=$($WP plugin install "$ZIP" --force 2>&1)
ok "upgrade 1.1.1 -> 1.1.2 in place, still active" "$([ "$($WP plugin get $SLUG --field=version)" = 1.1.2 ] && [ "$($WP plugin get $SLUG --field=status)" = active ] && echo 1)" "$OUT"
a is-good; a active
clean_log "in-place upgrade"

# --- admin HTTP flows: permissions, nonce/CSRF, validation ---------------------------------------------------------
J=/tmp/a.jar; E=/tmp/e.jar
login() { rm -f "$2"; curl -s -o /dev/null -c "$2" -b "$2" $B/wp-login.php; curl -s -o /dev/null -w '%{http_code}' -c "$2" -b "$2" --data-urlencode "log=$1" --data-urlencode "pwd=$3" -d testcookie=1 $B/wp-login.php; }
ok "admin login" "$([ "$(login qaadmin $J "$ADMIN_PASS")" = 302 ] && echo 1)"
CODE=$(curl -s -b $J "$PAGE" -o /tmp/s.html -w '%{http_code}')
ok "settings page 200" "$([ "$CODE" = 200 ] && echo 1)" "$CODE"
ok "settings page has exactly 3 slot sections" "$([ "$(grep -o 'name="revguard_free_settings\[slots\]\[[0-9]*\]\[slot\]"' /tmp/s.html | wc -l)" = 3 ] && echo 1)"
nonce() { grep -o 'name="_wpnonce" value="[a-f0-9]*"' "$1" | head -1 | sed 's/.*value="//;s/"//'; }
save() { local n=$1; shift; curl -s -o /dev/null -w '%{http_code}' -b $J -c $J -e "$PAGE" --data-urlencode option_page=revguard_free --data-urlencode action=update --data-urlencode "_wpnonce=$n" --data-urlencode "_wp_http_referer=/wp-admin/options-general.php?page=$SLUG" "$@" $B/wp-admin/options.php; }
VALID=(--data-urlencode "$O[publisher]=ca-pub-1234567890123456" --data-urlencode "$O[load_script]=1"
  --data-urlencode "$O[slots][1][enabled]=1" --data-urlencode "$O[slots][1][slot]=1111111111" --data-urlencode "$O[slots][1][position]=manual" --data-urlencode "$O[slots][1][paragraph]=2"
  --data-urlencode "$O[slots][2][enabled]=1" --data-urlencode "$O[slots][2][slot]=2222222222" --data-urlencode "$O[slots][2][position]=after_paragraph" --data-urlencode "$O[slots][2][paragraph]=3"
  --data-urlencode "$O[slots][3][enabled]=1" --data-urlencode "$O[slots][3][slot]=3333333333" --data-urlencode "$O[slots][3][position]=end" --data-urlencode "$O[slots][3][paragraph]=2"
  --data-urlencode "$O[slots][4][enabled]=1" --data-urlencode "$O[slots][4][slot]=4444444444")
ok "save valid settings (302)" "$([ "$(save "$(nonce /tmp/s.html)" "${VALID[@]}")" = 302 ] && echo 1)"
a is-good
ok "4th slot ignored (3-slot limit)" "$(! $WP option get $O --format=json | grep -q 4444444444 && echo 1)"
curl -s -b $J "$PAGE" -o /tmp/s.html
save "$(nonce /tmp/s.html)" --data-urlencode "$O[publisher]=\"><script>alert(1)</script>" --data-urlencode "$O[load_script]=1" \
  --data-urlencode "$O[slots][1][enabled]=1" --data-urlencode "$O[slots][1][slot]=1 onerror=alert(1)" \
  --data-urlencode "$O[slots][2][enabled]=1" --data-urlencode "$O[slots][2][slot]=2222222222" --data-urlencode "$O[slots][2][position]=after_paragraph" --data-urlencode "$O[slots][2][paragraph]=3" \
  --data-urlencode "$O[slots][3][enabled]=1" --data-urlencode "$O[slots][3][slot]=3333333333" --data-urlencode "$O[slots][3][position]=end" >/dev/null
curl -s -b $J "$PAGE&settings-updated=true" -o /tmp/s3.html
ok "XSS publisher + slot rejected with errors, prior kept, nothing reflected" "$(grep -q 'Publisher ID must look like' /tmp/s3.html && grep -q 'digits only' /tmp/s3.html && grep -q 'value="ca-pub-1234567890123456"' /tmp/s3.html && grep -q 'value="1111111111"' /tmp/s3.html && ! grep -q '<script>alert(1)' /tmp/s3.html && ! grep -q 'onerror=alert' /tmp/s3.html && echo 1)"
curl -s -b $J "$PAGE" -o /tmp/s.html
CODE=$(save "$(nonce /tmp/s.html)" --data-urlencode "$O[publisher][]=x" --data-urlencode "$O[load_script][a]=1" --data-urlencode "$O[slots][1][slot][]=1" \
  --data-urlencode "$O[slots][1][paragraph][x][y]=2" --data-urlencode "$O[slots][1][position][]=end" --data-urlencode "$O[slots][1][enabled][]=1" \
  --data-urlencode "$O[slots][2]=scalar" --data-urlencode "$O[slots][3][paragraph]=1e9" --data-urlencode "$O[discard_legacy][]=1")
ok "malformed nested-array POST handled (302, no 500)" "$([ "$CODE" = 302 ] && echo 1)" "$CODE"
S=$($WP option get $O --format=json)
ok "malformed POST kept valid IDs and bounded paragraph" "$(echo "$S" | grep -q '"publisher":"ca-pub-1234567890123456"' && echo "$S" | grep -q '"slot":"1111111111"' && echo "$S" | grep -q '"slot":"3333333333","position":"manual","paragraph":2' && echo 1)" "$S"
a set-good >/dev/null
ok "invalid nonce rejected (403)" "$([ "$(save badnonce "${VALID[@]}")" = 403 ] && echo 1)"
ok "missing nonce rejected (403)" "$([ "$(curl -s -o /dev/null -w '%{http_code}' -b $J -d option_page=revguard_free -d action=update -d "$O[publisher]=ca-pub-9999999999999999" $B/wp-admin/options.php)" = 403 ] && echo 1)"
login qaeditor $E "$ED_PASS" >/dev/null
ok "editor blocked from settings page (403)" "$([ "$(curl -s -o /dev/null -w '%{http_code}' -b $E "$PAGE")" = 403 ] && echo 1)"
EN=$(curl -s -b $E $B/wp-admin/profile.php | grep -o 'name="_wpnonce" value="[a-f0-9]*"' | head -1 | sed 's/.*value="//;s/"//')
ok "editor blocked from options.php save (403)" "$([ "$(curl -s -o /dev/null -w '%{http_code}' -b $E -d option_page=revguard_free -d action=update -d _wpnonce=$EN -d "$O[publisher]=ca-pub-9999999999999999" $B/wp-admin/options.php)" = 403 ] && echo 1)"
CODE=$(curl -s -o /dev/null -w '%{http_code}' -d option_page=revguard_free -d action=update -d "$O[publisher]=ca-pub-9999999999999999" $B/wp-admin/options.php)
ok "anonymous options.php save blocked (redirect to login)" "$(case $CODE in 302|403) echo 1;; esac)" "$CODE"
ok "direct access to includes/plugin.php outputs nothing" "$([ -z "$(curl -s $B/wp-content/plugins/$SLUG/includes/plugin.php)" ] && [ -z "$(curl -s $B/wp-content/plugins/$SLUG/$SLUG.php)" ] && echo 1)"
a is-good
clean_log "admin flows"

# --- front-end rendering -------------------------------------------------------------------------------------------
order() { grep -oE 'Para (one|two|three|four)|data-ad-slot="[0-9]+"' "$1" | tr '\n' ' '; }
curl -s $B/qa-post/ -o /tmp/p.html
ok "post: para3 -> slot2, shortcode slot1, end slot3; id=4 renders nothing" "$([ "$(order /tmp/p.html)" = 'Para one Para two Para three data-ad-slot="2222222222" Para four data-ad-slot="1111111111" data-ad-slot="3333333333" ' ] && echo 1)" "$(order /tmp/p.html)"
T=$(grep -o '<script[^>]*adsbygoogle.js[^>]*>' /tmp/p.html)
ok "exactly one AdSense tag, async + crossorigin, no ver" "$([ "$(grep -c 'adsbygoogle.js' /tmp/p.html)" = 1 ] && echo "$T" | grep -q '<script async crossorigin="anonymous" ' && echo "$T" | grep -qE "adsbygoogle.js\?client=ca-pub-1234567890123456[\"']" && ! echo "$T" | grep -q 'ver=' && echo 1)" "$T"
ok "front.js loaded from new plugin dir" "$(grep -q "plugins/$SLUG/assets/front.js" /tmp/p.html && echo 1)"
ok "no other external scripts/iframes" "$([ "$(grep -oiE '<(script|iframe)[^>]+src="https?://[^"]+' /tmp/p.html | grep -v '//localhost' | grep -vc 'pagead2.googlesyndication.com')" = 0 ] && echo 1)"
curl -s $B/qa-page/ -o /tmp/g.html; ok "page gets automatic slot" "$(grep -q 'data-ad-slot="3333333333"' /tmp/g.html && echo 1)"
curl -s $B/feed/ -o /tmp/f.xml; ok "feed has no ads" "$(grep -q '<rss' /tmp/f.xml && ! grep -q 'adsbygoogle' /tmp/f.xml && echo 1)"
curl -s $B/ -o /tmp/h.html; ok "home/archive: no automatic slots" "$(! grep -q 'data-ad-slot="2222222222"' /tmp/h.html && ! grep -q 'data-ad-slot="3333333333"' /tmp/h.html && echo 1)"
curl -s -b $J $B/wp-admin/ -o /tmp/ad.html; ok "no AdSense tag in admin" "$(! grep -q adsbygoogle.js /tmp/ad.html && echo 1)"
wpq option update qa_dup_script 1; curl -s $B/qa-post/ -o /tmp/d.html
ok "duplicate prevention: theme tag kept, own copy skipped" "$([ "$(grep -c 'adsbygoogle.js' /tmp/d.html)" = 1 ] && grep -qE "id=[\"']theme-adsense-js" /tmp/d.html && ! grep -qE "id=[\"']revguard-free-adsense-js" /tmp/d.html && echo 1)" "$(grep -o '<script[^>]*adsbygoogle.js[^>]*>' /tmp/d.html)"
wpq option delete qa_dup_script
$WP eval '$s=get_option("revguard_free_settings"); $s["load_script"]=false; update_option("revguard_free_settings",$s);' >/dev/null
curl -s $B/qa-post/ -o /tmp/n.html; ok "load_script off: units rendered, no Google tag" "$(grep -q 'data-ad-slot="1111111111"' /tmp/n.html && ! grep -q 'adsbygoogle.js' /tmp/n.html && echo 1)"
a set-good >/dev/null
clean_log "front-end"

# --- corrupted stored settings --------------------------------------------------------------------------------------
for m in corrupt-object corrupt-raw incomplete-class; do
  a $m >/dev/null
  P=$(curl -s -o /dev/null -w '%{http_code}' $B/qa-post/); G=$(curl -s -o /dev/null -w '%{http_code}' -b $J "$PAGE"); D=$(curl -s -o /dev/null -w '%{http_code}' -b $J $B/wp-admin/)
  ok "$m: post/settings/dashboard return 200" "$([ "$P$G$D" = 200200200 ] && echo 1)" "$P $G $D"
  a normalized-ok
  clean_log "$m"
done
curl -s -b $J "$PAGE" -o /tmp/s.html
ok "settings page re-save after corruption works" "$([ "$(save "$(nonce /tmp/s.html)" "${VALID[@]}")" = 302 ] && echo 1)"
a is-good

# --- coexistence with older builds, both activation orders ---------------------------------------------------------
coexist() { # $1 label, $2 zip, $3 plugin dir name to use
  local dir=$3 tmp=/tmp/legacy-$3
  rm -rf $tmp && mkdir -p $tmp && unz "$2" $tmp
  rm -rf /var/www/html/wp-content/plugins/$dir && mv $tmp/* /var/www/html/wp-content/plugins/$dir && chown -R www-data:www-data /var/www/html/wp-content/plugins/$dir
  local main; main=$(cd /var/www/html/wp-content/plugins/$dir && ls *.php | head -1)
  for ord in "kadirslot-first" "legacy-first"; do
    wpq plugin deactivate $SLUG; wpq plugin deactivate $dir/$main; a set-good >/dev/null
    if [ $ord = kadirslot-first ]; then O1=$($WP plugin activate $SLUG 2>&1); O2=$($WP plugin activate $dir/$main 2>&1);
    else O1=$($WP plugin activate $dir/$main 2>&1); O2=$($WP plugin activate $SLUG 2>&1); fi
    ok "$1 [$ord]: both activate without fatal" "$(echo "$O1$O2" | grep -c 'activated' | grep -qx 2 && ! echo "$O1$O2" | grep -qi fatal && echo 1)" "$O1 | $O2"
    a passive
    curl -s $B/qa-post/ -o /tmp/c.html
    local units; units=$(grep -o 'data-ad-slot="[0-9]*"' /tmp/c.html | wc -l)
    ok "$1 [$ord]: single engine (no KadirSlot output, <=3 units, <=1 Google tag)" "$(! grep -q "plugins/$SLUG/assets/front.js" /tmp/c.html && [ "$units" -le 3 ] && [ "$(grep -c 'adsbygoogle.js' /tmp/c.html)" -le 1 ] && echo 1)" "units=$units"
    ok "$1 [$ord]: admin notice tells the admin to deactivate the older plugin" "$(curl -s -b $J $B/wp-admin/plugins.php | grep -q 'KadirSlot Ad Placement is paused' && echo 1)"
    a is-good
  done
  wpq plugin deactivate $dir/$main; rm -rf /var/www/html/wp-content/plugins/$dir
  curl -s $B/qa-post/ -o /tmp/c.html
  ok "$1: after removing older build KadirSlot renders again" "$([ "$(grep -o 'data-ad-slot="[0-9]*"' /tmp/c.html | wc -l)" = 3 ] && grep -q "plugins/$SLUG/assets/front.js" /tmp/c.html && echo 1)"
  clean_log "$1 coexistence"
}
coexist "RevGuard Free 1.1.0" /qa/fixtures/revguard-free-1.1.0.zip revguard-free
coexist "RevGuard Free 1.0.1" /qa/fixtures/revguard-free-1.0.1.zip revguard-free
coexist "KadirSlot 1.1.1 second copy" /qa/fixtures/kadirslot-ad-placement-1.1.1.zip kadirslot-ad-placement-old
# Two copies of 1.1.2 in different directories.
rm -rf /var/www/html/wp-content/plugins/kadirslot-copy && cp -r /var/www/html/wp-content/plugins/$SLUG /var/www/html/wp-content/plugins/kadirslot-copy
OUT=$($WP plugin activate kadirslot-copy/$SLUG.php 2>&1)
ok "second 1.1.2 copy activates without fatal" "$(echo "$OUT" | grep -q activated && ! echo "$OUT" | grep -qi fatal && echo 1)" "$OUT"
curl -s $B/qa-post/ -o /tmp/c.html
ok "two 1.1.2 copies: ads rendered once" "$([ "$(grep -o 'data-ad-slot="[0-9]*"' /tmp/c.html | wc -l)" = 3 ] && [ "$(grep -c 'adsbygoogle.js' /tmp/c.html)" = 1 ] && echo 1)"
wpq plugin deactivate kadirslot-copy/$SLUG.php; rm -rf /var/www/html/wp-content/plugins/kadirslot-copy
clean_log "duplicate copy"

# --- RevGuard Pro coexistence (only for packages present in /qa/extra) ---------------------------------------------
for PZ in /qa/extra/revguard-pro-*.zip; do
  [ -f "$PZ" ] || continue
  PV=$(php -r '$z=new ZipArchive; $z->open($argv[1]); echo $z->getFromName("revguard-pro/revguard-pro.php");' "$PZ" | sed -n 's/^ \* Version: *//p')
  if ! ver_ge "$WPV" 6.5; then echo "SKIP RevGuard Pro $PV needs WordPress 6.5+ (running $WPV)"; continue; fi
  for ord in kadirslot-first pro-first; do
    rm -rf /var/www/html/wp-content/plugins/revguard-pro; wpq plugin deactivate $SLUG; a set-good >/dev/null
    for opt in $($WP option list --search='revguard_pro*' --field=option_name 2>/dev/null); do wpq option delete $opt; done
    wpq plugin install "$PZ"
    if [ $ord = kadirslot-first ]; then O1=$($WP plugin activate $SLUG 2>&1); O2=$($WP plugin activate revguard-pro 2>&1);
    else O1=$($WP plugin activate revguard-pro 2>&1); O2=$($WP plugin activate $SLUG 2>&1); fi
    ok "Pro $PV [$ord]: both activate without fatal" "$(echo "$O1$O2" | grep -c 'activated' | grep -qx 2 && ! echo "$O1$O2" | grep -qi fatal && echo 1)" "$O1 | $O2"
    a pro
    curl -s $B/qa-post/ -o /tmp/c.html
    # Pro renders its own (imported) units; KadirSlot must add nothing: no wrapper, no script handle, no front.js.
    ok "Pro $PV [$ord]: KadirSlot adds no markup or scripts on the front end" "$(! grep -q "plugins/$SLUG/assets/front.js" /tmp/c.html && ! grep -q 'data-revguard-free-slot=' /tmp/c.html && ! grep -qE "id=[\"']revguard-free-adsense-js" /tmp/c.html && echo 1)"
    echo "INFO Pro $PV [$ord]: Pro output units=$(grep -o 'data-ad-slot="[0-9]*"' /tmp/c.html | wc -l) google-tags=$(grep -c 'adsbygoogle.js' /tmp/c.html)"
    ok "Pro $PV [$ord]: KadirSlot settings page explains Pro handles rendering" "$(curl -s -b $J "$PAGE" | grep -q 'compatible premium plugin is active' && echo 1)"
    wpq plugin deactivate revguard-pro
    a is-good
    curl -s $B/qa-post/ -o /tmp/c.html
    ok "Pro $PV [$ord]: after Pro deactivation KadirSlot renders again" "$([ "$(grep -o 'data-ad-slot="[0-9]*"' /tmp/c.html | wc -l)" = 3 ] && echo 1)"
    clean_log "Pro $PV $ord"
  done
  rm -rf /var/www/html/wp-content/plugins/revguard-pro
done

# --- external requests ---------------------------------------------------------------------------------------------
ok "no server-side HTTP requests made by $SLUG" "$(! grep -q "	$SLUG	" /qa/artifacts/http-requests.log 2>/dev/null && echo 1)"

# --- deactivate / uninstall behavior -------------------------------------------------------------------------------
wpq plugin deactivate $SLUG; a is-good
wpq plugin delete $SLUG
ok "delete plugin keeps settings (documented: no uninstall cleanup)" "$($WP option get $O --format=json | grep -q 1111111111 && echo 1)"
clean_log "final"
