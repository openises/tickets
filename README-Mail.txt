Readme for Tickets Mail
=====
+ The two files in here add a capability to email ticket information on version 2.4.
+ One of the files, functions.inc.php, replaces its namesake, while the other is new.
+ I've tested this under Apache/*nix, but I don't have a way to do so under IIS.
  If any of you are interested in that, try it and let me know.  There are known differences,
  especially in the internal handling of line-end characters.
+ You get to mail via the 'This Ticket' mini nav-bar.
+ The first time you bring up mail, you'll see that it asks for a 'reply-to' entry.  Fill
  that in with whatever address you'd like recipient replies, if any,to go.  Presumably
  your own address.
+ That 'reply-to' address is remembered, for convenience.  If you do need to change it,
  do so in the CONFIGURATION/Edit Settings list.
+ It really could use a facility whereby you cd maintain a list of commonly-used addresses,
  and a subsequent release will include that.  In the meanwhile, bear with me on this.  
+ Th, th, that's all, folks.

-AS