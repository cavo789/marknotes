#Highlite

###About

Highlite is a small jQuery plugin that highlights searched text in the selected 
element.

###Usage

```javascript
$(function () {
    $("#search").on("search change keyup", function () {
        var text = this.value;
        $("#content").highlite({
            text: text
        });
    });
});
```

###Plugin options

```
text
```
Text to be searched and highlighted. 
