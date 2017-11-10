# Marknotes - Plugins - Content

**Reminder (see ../readme.md for more info about plugins)**

Content plugins can only implement the `render.content` event. Nothing else.

If you need to add JS or CSS, you'll need to use /plugins/pages/xxx plugins who can interact with the page.

Content plugins will only be able to interact with the HTML rendering of a note (therefore `render.content`) and only that.
