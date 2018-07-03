# Privacy and protection : everything is yours, for now and always

marknotes doesn't contain any "call-home" functionality or things like that : by using marknotes on your server, you've everything under your control.

Notes are hosted on your server and remain your entire property; no one will have access to them (except your hoster if your site is online).

If, like me, you're using marknotes for also storing credentials (login, passwords, URLs, ...) or private data (your address, birthdate, customer number for suppliers, ...), consider the use of the `encrypt` plugin.

## Privacy

### Encryption

You'll find in the `settings.json.dist` file this entry :

```json
{
 "plugins": {
  "options": {
	"markdown": {
	"encrypt": {
	 "password": "",
	 "method": "aes-256-ctr"
	}
	}
  }
 }
}
```

By just specifying a password in your `settings.json` file like the one below, the encrypt plugin parameterization is done.

```json
{
 "plugins": {
  "options": {
	"markdown": {
	"encrypt": {
	 "password": "My#Gre@tPasSWorD"
	}
	}
  }
 }
}
```

When the plugin is enabled (by default) and configured (as explained here above), you can encrypt note's content.

Just type your confidential data, in your markdown file like this :

> `<encrypt>This text is secret, no one can read it unless if allowed</encrypt>`

The first time the note will be displayed in marknotes, any `<encrypt>` tag will be converted into an encrypted content.

The encrypted line will become :

> `<encrypt data-encrypt="true">U%0B%87L%B7%A3%8D%BEKC%09%0E%F7s%A1%80Y8VuAjvD6HN1DiE0H7G1w2USVSeiWTKHQR2c4iTbiDrcZj1bk8w6DvfCzL5Yrsw5gM69UTDRWmV4I9w%3D</encrypt>`

No one will be able to read the sentence anymore ... except by starting marknotes and displaying the note through marknotes's interface.

Your hoster f.i. will, of course, have access to the file (for backup purposes) but won't be able to unencrypt the content; only you and allowed people.

### Allowed people

By default, your marknotes interface is accessible publicly but, if you've an encrypted part, that part will appear as "This information is encrypted" when your reader is not logged on.

The encryption plugin will decrypt things for, only, people who've logged on your site.

You can enable the login plugin (enabled by default) by adjusting properties in the `settings.json.dist` file. Here is the default login / password :

```json
{
	"plugins": {
		"options": {
			"task": {
				"login": {
					"username": "admin",
					"password": "marknotes",
					"enabled" : 1
				}
			}
		}
	}
}
```

Create (or update) your `settings.json` file and specify your own login / password like, f.i. :

```json
{
	"plugins": {
		"options": {
			"task": {
				"login": {
					"username": "Chr@st#pH€",
					"password": "29:&9)-q{f[46Dh?2-dYK'WxN&:@Vr}')-+"
				}
			}
		}
	}
}
```

*You can, of course, enable the debug mode from the settings screen of marknotes.*

From now, encrypted content won't be decoded unless your reader makes a login with these credentials. Please note, thus, that the note will be accessible if only a portion is encrypted.

Read more information by reading the documentation of the **Encrypt markdown plugin**.

### Self hosted

Unlike software based on the cloud (like Evernote™, OneNote™, Dropbox™, GDrive™ and many other solutions), marknotes don't rely on cloud capabilities : notes are stored entirely on your system and that system can be your hard disk.

Marknotes is a perfect solution to run on your localhost system and has no dependencies on external resources like a CDN host.

This makes marknotes a solution that can run offline.

### 100% Open Source

If you still have a doubt, just read the source code since everything is open. marknotes is an Open Source solution with no encrypted part and, also, the code contains a lot (a lot!) of comments to make things clear enough for a non-programmer.

If you still have a question, feel free to contact me.

## Protection

### Don't show notes to everyone

You can go one step further concerning your protection : protect your interface.

Marknotes is a PHP application and use a `.htaccess` file. With Apache (also on localhost), you can define a .htpasswd file to restrict the use of the website to only valid users. If you're not familiar with .htpasswd, please read any tutorials on the internet like, f.i., [https://openclassrooms.com/courses/concevez-votre-site-web-avec-php-et-mysql/proteger-un-dossier-avec-un-htaccess](https://openclassrooms.com/courses/concevez-votre-site-web-avec-php-et-mysql/proteger-un-dossier-avec-un-htaccess) (in French).

There is an easy tool for generating such file : [http://aspirine.org/htpasswd.html](http://aspirine.org/htpasswd.html) (French)

By adding a .htpasswd file to your website, you'll be able to define users like `Christophe`, `Simon`, `Marc`, ... (or only one user). Each defined user will have his own password (in that case, when you decide to remove access to an user, it's easy, just remove his entry in the .htpasswd file).

By putting a .htpasswd file in place, no one will be able to access to your interface anymore. This is a protection of Apache; not on the application-level but Operating system one. This is a robust protection.

### Don't show specific folders to your user.

You've added a .htpasswd file; you can go one level deeper in your protection : decide to show all folders to every logged on users but not your "confidential" folder.

To protect a folder (won't be visible to un-allowed people), just add a settings.json file in the parent folder with, f.i.

```json
{
	"plugins": {
		"options": {
			"task": {
				"acls": {
					 "folders": {
					  	"confidential": ["Christophe", "Simon"]
					 }
				}
			}
		}
	}
}
```

`Christophe` and `Simon` are two users defined in the .htpasswd file. So, only for users who've used the credentials of Christophe and of Simon will be able to see the `confidential` folder. For the other users (`Marc` in our example), that folder won't appear at all, they just don't know that the folder exists.

Read more information by reading the documentation of the **ACLs task plugin**.
