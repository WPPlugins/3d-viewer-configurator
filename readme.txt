=== 3D Produkt Viewer ===
Contributors: visualtektur, ProNego
Donate link: http://3d-viewer-freeware.eu/freeware/plugin-fuer-wordpress.html
Tags: 3d, viewer, configurator, eshop, products, 360
Requires at least: 1.0
Tested up to: 4.0
Stable tag: 1.8.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The 3D Produkt Viewer provides an interactive 3D display of products and objects on a website. It works in all today's major browsers.


== Description ==

The 3D Produkt Viewer provides an interactive 3D display of products and objects directly in the browser. The configurator visually helps to mature purchasing decisions from the first moment because many customers get their questions already answered about a product by seeing it interactively.

The 3D Produkt Viewer is a new and unique presentation technology for all products. On the very first visit of your website or eShop, the user gets an intense Aha experience which strongly, positively distinguishes your website from your competitors.

Dealers and salespeople get the 3D Viewer Konfigurator is an effective marketing tool and advice on hand. It is accessible over the Internet and operable by its cross-system functionality with all major end devices.

No matter if you have an iPhone, iPad, Android, a Mac or PC, the 3D Viewer simply runs.


== Installation ==

There are several possibilities to upload the images:

Option 1:
Upload a zip archive, which contains the complete folder structure 
for several configurations (an example of the required structure is
displayed in the backend)

Option 2:
Create a new configuration in the backend by specifying a name in the first step.

The next step is to upload the images, grouped by their area 
(360&deg; view small / zoom / 1 thumbnail). The images can be uploaded single,
or in groups (select multiple images at once in the select dialog window),
or as a zip file of pictures (the folder structure doesn't matter then).

IMPORTANT: The total size of uploadable file(s) depends on the 
server-side parameters described in section "conditions".


== Screenshots ==

1. Object unfolded
2. Object collapsed
3. Object unfolded from other perspective
4. Overview of differen configurator elements
5. Management of small pictures, zoom pictures, thumbnail used for selection
6. More screenshots are available on: http://3d-viewer-freeware.eu/installation/fuer-wordpress.html

== Changelog ==

= 1.8.1 =
Fixed minor CSS bug.

= 1.8 =
Checked and established WP 4.0 compatibility, added icon to admin menu.

= 1.7.3 =
Fixed SQL bug which prevented the database table to be auto-upgraded.
Fixed typo.

= 1.7.2 =
Updated readme, see below.

<b style="color: red;">Important Note for the people
having a previous version installed:</b>
Please manually backup the folder <code>"data"</code> located in 
<code>".../wp-content/plugins/3d-viewer-configurator/"</code>
before auto-upgrading.
After the upgrade, restore the contents of <code>"data"</code>
back to the original position.

Starting from release 1.7.2, this manual backup
is no longer necessary during auto-upgrade.


= 1.7.1 =
Important bugfix release:
- Fixed automatic deletion of configuration data when using Wordpress' plugin auto-upgrade function.
- Fixed a Paypal error.
- Fixed some typos.

= 1.7 =
- Bugfix in zoom mode.
- Changed name to 3D Produkt Viewer.
- New license system, Paypal integration.
- Added language file for DE.

Important: PRO version state is kept for future releases!
Existing owners of a PRO version please contact
<a href="mailto:info@visualtektur.de">info@visualtektur.de</a>
to get a serial number.

= 1.6 =
Configuration data is now kept during update procedure 
(does not work for releases < 1.6, please backup manually).

= 1.5.2 =
Minor readme modifications.

= 1.5.1 =
Updated readme, names, added link to example 3D data, added screenshots.

= 1.5 =
The zoom image now opens full screen.

= 1.4 =
Now suitable for Wordpress 3.4.

= 1.3 =
Now with SuperZOOM

= 1.2 =
A better design

= 1.1 =
Fixed some small errors

== Upgrade Notice ==

WARNING: Removing the plugin from the wordpress backend causes
all configurations and images to be deleted.
If you want to keep the configurations, first connect via FTP to your 
Wordpress installation, and backup the directory 'data' located
in "wp-content/plugins/wp_vtpkonfigurator_<version>/".
Then, you simply deactivate and remove the old plugin version in
the Wordpress backend and install the new version.
Finally, upload the backup data again to the same location.
Don't hestitate to contact us if you have questions, we'll be happy
to help.
Email: info@visualtektur.net


== Conditions ==

The functionality of the plugin depends on some settings of the server-side
PHP configuration. For proper operation, the following settings
in the appropriate PHP configuration file (usually a file named php.ini)
may need to be adjusted. Please contact your provider or the appropriate 
technician.

- Parameter "post_max_size":
   Sets the maximum allowed file size for file uploads through forms using POST
  (The smaller value of post_max_size / upload_max_filesize is taken)

- Parameter "upload_max_filesize": 
   States generally allow the maximum file size for uploads
  (The smaller value of post_max_size / upload_max_filesize is taken)

- Parameters "max_file_uploads":
   Determines the maximum number of individual files that can be uploaded
   within a file upload operation

- Parameters "max_execution_time":
   Timeout in seconds which a script is allowed to spend for the processing of 
   data uploaded via POST / GET. If this limit is exceeded, the execution of
   the script will be canceled. After uploading the files in the archive 
   unpacked and moved to the right place. If this value is set too low, 
   the upload procedure cannot be executed properly.


== Additional Comments ==

- By using "Option 1", all top-level directories are interpreted as
   configurations. The only exception is the directory "__MACOSX"
   which is typically created automatically by Apple computers.
   Furthermore, all files starting with a dot (.), eg .DS_Store, will be ignored.

- Using "Option 2" allows to upload multiple images or a zip file full of pictures.
  If a Zip file is selected, all images will be added to the current range
  (360&deg; / zoom, respectively), regardless of the folder structure inside the zip file.
  If there are multiple images with identical filenames in different folders in the zip file,
  the last file overwrites previously processed files of the same name (alphabetical order and
  recursively).

= Description of the data =

<code>
item1/ - Configuration 1, any name (only letters, numbers and "-", "_" allowed)
             thumb.png - small preview
                  view/ - images for the animation numbered, with optional prefix
                     1.jpg
                     2.jpg
                     3.jpg
                     ...
                     n.jpg
                  zoom/
                     1.jpg - Zoom images in high resolution, matching the small screen
                     2.jpg
                     3.jpg
                     ...
                     n.jpg

item2/ - Configuration 2
        ...
item3/
        ...
...
/itemn
        ...
</code>