# Dugong

## Kind of ugly mind-mapping in javascript and html

Dugong is a tiny tiny (tiny) library in javascript to turn a plaintext indented list (text outline) into a shiny SVG-type mindmap, right in your browser.

![](preview.png)

## Installation

Download dugong.js or dugong.js.min from this repo. Include it in the relevant web page by the normal means.

## Usage

Include all of your dugong lists in divs with a given class - for example, if you want to use the class "dugong-src":

```html
<div class="dugong-src">
Root node
	Child node
	Another child
		Some grandchildren
		Go here
	A third child
		And another grandchild
</div>
```

Now, somewhere in your code, call `Dugong.populate(className)`:

```html
<script>
Dugong.populate("dugong-src");
</script>
```

## Styling

You can style dugong mindmaps using CSS. Every dugong mindmap is stored inside an `svg` with the class `dugong`. Connectors are elements of tag name `path`. Nodes are made of a `rect` and `foreignObject` (which in turn contains a `div`), both grouped together within a `g` element. Every `g` and `path` node also has a class indicating its "generation": the root node is class `gen-0`, first children and their connectors are `gen-1`, etc. etc.

See `dugong.css` for a basic stylesheet.

## Customizing

You can customize dugong.js by altering a variety of proprties before calling `Dugong.populate()`. These include:

* `Dugong.stalkLength`: Distance between nodes [default: 200]
* `Dugong.boxMinHeight`: Minimum height of each node box [default: 50]
* `Dugong.boxRatio`: Width-to-height ratio of node boxes [default: 2]
* `Dugong.canvasMargin`: Margin to leave around the outside of the map [default: 100]
* `Dugong.boxMargin`: Margin to leave between each node box and the text it contains [default: 10]

## Hacking

Dugong is released using the MIT license, so feel free to modify as appropriate for use in your projects should you wish.