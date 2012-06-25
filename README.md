phpajaxshell
============

Forced to use a web host that doesn't offer shell access? Need to do some command-line stuff like unzipping, recursively cloning directories or rsyncing from a remote host?

If so, you may find phpajaxshell useful. 


Features
----------------

* Command and filename auto-completion
* History support
* Streamed output
* Simple interface


Quick start
----------------

You need to do a tiny bit of setting up before you can use phpajaxshell.

1. Download the zip and unzip it somewhere. 
2. Generate a hash of your chosen password using the included `password_hash.php` file.
3. Edit `config.php` to add your username and hashed password
4. Upload the whole directory to your web host
5. Access the directory on the remote host with your web browser.

Notes
---------------
`phpajaxshell` just takes what you enter and passes it to the server to execute. You can't use interactive programs like vi, as they require real terminal emulation.

The password hashing code uses Blowfish, so that must be installed on the remote server (and the local machine if you use that to run `password_hash.php`.

Caveats
---------------
It's not a great idea to leave software like this installed on a public-facing server. Once you've done what you need to do, I recommend deleting the `phpajaxshell` directory from the server. There is no protection against an attacker brute-forcing the password. 

Alternatives
---------------
[TinyShell](http://www.5p.dk/tinyshell/) - Uses a different approach of building commands on the client side with javascript. No command completion. No output streaming. Can't paste into command-line.

[PHPTerm](http://sourceforge.net/projects/phpterm/) - No command or file completion. No output streaming. Generates many warnings.

[PHPShell](http://phpshell.sourceforge.net/) - The grandaddy. Not AJAX. No completion or streaming.

End-user license agreement
------------------------
You agree not use this software for evil purposes.

License
--------------
Licensed under the [BSD 3-clause licence](http://opensource.org/licenses/BSD-3-Clause).
© Copyright Chris How, 2012. 

