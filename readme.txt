=== Attending Users ===
Contributors: menian
Tags: attending list, attend, event, event list, add users, present users, supporting users
Requires at least: 2.7
Tested up to: 3.3.1.
Stable tag: 1.0

Attending Users helps you add a list under your post, where users can add themselves as Attending. It's great for event post.

== Description ==

As web site owner, Attending Users will help you in adding an attending list to the bottom of your posts. Registered users can click on the Submit button of the list in order to have their names added or removed. User names act as author page links, no matter how you have set your Permalinks.

= Usage: =

After you install the plugin, you can go to the settings page and change them as you like. In the Settings page 
you can add support for your custom content types, add new titles to chose from when creating attending lists or change the labels of buttons (ex. Submit, unsubscribe). A new meta-box, called Attending List, will be present (meta-box is for example is the box for tags or categories) when you go to create a new post or edit existing one. From that meta-box you can chose a title for your list. When the post is saved/published the list will be added at the bottom of the page. To remove already created list: select the empty option for title. Any logged in user can click on the button for Attending at a list. Not registered users will see a message, telling them that they have to register if they want to have their names added to the list.

= What should you know more? =

* If a user has both First and Last name added to his profile, than both of them will be presented on a list. If one of those names is not filled in, than the login name is used. The idea is to keep all names on the list unique.
* The plugin removes it's database tables and options when deleted.
* The plugin will work with custom database prefix.
* The plugin has a Class with unique name. By doing so, the chances of conflict with functions from other plugins has been reduced.
* Every function starts with a comment explaining what it is supposed to do.
* There is plugin version defined in the code, which will make it easier for further updates.

= Incompatibility: =

Attending Users doesn't work as expected if <a href="http://wordpress.org/extend/plugins/like/">Like</a> plugin is active. The result of 
incompatibility is in adding twice the user name to a list. No solution has been found yet. You can try using <a href="http://wordpress.org/extend/plugins/simple-social-buttons">Simple Social 
Buttons</a> instead of Like. Please, report if you find any other plugins causing problems.

= List of posts where user has clicked Attend on author's template =

At the end of the plugin, I've shared few lines of code (commented out) that can improve your author's template. That code will unlock the feature of listing all posts where a user has clicked Attend. Copy the code and paste it right before the loop of your author.php file.

== Installation ==

1. Download the latest version
2. Extract it in the /wp-content/plugins/ directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to Settings -> Att Users Settings and create your first title
5. Go to Add new / Edit post and create your first attending list

== Screenshots ==

1. Settings page, where you can define titles, add supp
2. That's the meta-box where you add a list to a post
2. That's how the list looks like when created