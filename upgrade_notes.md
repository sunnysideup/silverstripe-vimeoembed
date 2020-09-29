2020-09-29 11:00

# running php upgrade upgrade see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/ss3/upgrades/vimeoembed
php /var/www/ss3/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code upgrade /var/www/ss3/upgrades/vimeoembed/vimeoembed  --root-dir=/var/www/ss3/upgrades/vimeoembed --write -vvv
Writing changes for 5 files
Running upgrades on "/var/www/ss3/upgrades/vimeoembed/vimeoembed"
[2020-09-29 23:00:06] Applying RenameClasses to VimeoembedTest.php...
[2020-09-29 23:00:06] Applying ClassToTraitRule to VimeoembedTest.php...
[2020-09-29 23:00:06] Applying RenameClasses to VimeoDataObjectRedoTask.php...
[2020-09-29 23:00:06] Applying ClassToTraitRule to VimeoDataObjectRedoTask.php...
[2020-09-29 23:00:06] Applying RenameClasses to VimeoDataObject.php...
[2020-09-29 23:00:06] Applying ClassToTraitRule to VimeoDataObject.php...
[2020-09-29 23:00:06] Applying RenameClasses to VimeoDOD.php...
[2020-09-29 23:00:06] Applying ClassToTraitRule to VimeoDOD.php...
[2020-09-29 23:00:06] Applying RenameClasses to VimeoDataObjectAdmin.php...
[2020-09-29 23:00:06] Applying ClassToTraitRule to VimeoDataObjectAdmin.php...
[2020-09-29 23:00:06] Applying RenameClasses to _config.php...
[2020-09-29 23:00:06] Applying ClassToTraitRule to _config.php...
modified:	tests/VimeoembedTest.php
@@ -1,4 +1,6 @@
 <?php
+
+use SilverStripe\Dev\SapphireTest;

 class VimeoembedTest extends SapphireTest
 {

modified:	src/Tasks/VimeoDataObjectRedoTask.php
@@ -2,9 +2,13 @@

 namespace Sunnysideup\Vimeoembed\Tasks;

-use BuildTask;
-use VimeoDataObject;
-use DB;
+
+
+
+use Sunnysideup\Vimeoembed\Model\VimeoDataObject;
+use SilverStripe\ORM\DB;
+use SilverStripe\Dev\BuildTask;
+


 class VimeoDataObjectRedoTask extends BuildTask

modified:	src/Model/VimeoDataObject.php
@@ -2,12 +2,18 @@

 namespace Sunnysideup\Vimeoembed\Model;

-use DataObject;
-use LiteralField;
-use ReadonlyField;
-use DBField;
-use Convert;
+
+
+
+
+
 use Exception;
+use SilverStripe\Forms\LiteralField;
+use SilverStripe\Forms\ReadonlyField;
+use SilverStripe\ORM\FieldType\DBField;
+use SilverStripe\Core\Convert;
+use SilverStripe\ORM\DataObject;
+


 /**

modified:	src/Model/VimeoDOD.php
@@ -2,12 +2,20 @@

 namespace Sunnysideup\Vimeoembed\Model;

-use DataExtension;
-use FieldList;
-use DropdownField;
-use LiteralField;
-use Config;
+
+
+
+
+
 use Page;
+use Sunnysideup\Vimeoembed\Model\VimeoDataObject;
+use SilverStripe\Forms\FieldList;
+use SilverStripe\Forms\DropdownField;
+use SilverStripe\Forms\LiteralField;
+use SilverStripe\Core\Config\Config;
+use Sunnysideup\Vimeoembed\Model\VimeoDOD;
+use SilverStripe\ORM\DataExtension;
+



@@ -22,7 +30,7 @@
 class VimeoDOD extends DataExtension
 {
     private static $has_one = array(
-        "VimeoDataObject" => "VimeoDataObject"
+        "VimeoDataObject" => VimeoDataObject::class
     );

     private static $exclude_vimeo_from_page_classes = [];
@@ -49,11 +57,11 @@
         $hasVimeo = true;
         $includeClasses = $this->owner->Config()->get("include_vimeo_in_page_classes");
         if (count($includeClasses)) {
-            if (!in_array($this->owner->ClassName, Config::inst()->get("VimeoDOD", "include_vimeo_in_page_classes"))) {
+            if (!in_array($this->owner->ClassName, Config::inst()->get(VimeoDOD::class, "include_vimeo_in_page_classes"))) {
                 $hasVimeo = false;
             }
         }
-        $excludeClasses = Config::inst()->get("VimeoDOD", "exclude_vimeo_from_page_classes");
+        $excludeClasses = Config::inst()->get(VimeoDOD::class, "exclude_vimeo_from_page_classes");
         if (count($excludeClasses)) {
             if (in_array($this->owner->ClassName, $excludeClasses)) {
                 $hasVimeo = false;

modified:	src/Cms/VimeoDataObjectAdmin.php
@@ -2,7 +2,10 @@

 namespace Sunnysideup\Vimeoembed\Cms;

-use ModelAdmin;
+
+use Sunnysideup\Vimeoembed\Model\VimeoDataObject;
+use SilverStripe\Admin\ModelAdmin;
+


 /**
@@ -12,7 +15,7 @@

 class VimeoDataObjectAdmin extends ModelAdmin
 {
-    private static $managed_models = array("VimeoDataObject");
+    private static $managed_models = array(VimeoDataObject::class);

     private static $url_segment = "vimeos";


Writing changes for 5 files
✔✔✔