# Marknotes - Plugins - Page

**Reminder (see ../readme.md for more info about plugins)**

Page plugins CAN'T INTERACT with the HTML rendering of the note (so they can't implement the  `render.content` event).

If you need to be able to interact with the HTML rendering, please use content plugins (/plugins/content/xxx plugins).

Page plugins can implement one, two of three of these events  :

1. `render.css`
2. `render.html`
3. `render.js`
