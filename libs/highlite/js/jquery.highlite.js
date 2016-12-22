;(function ($, window, document) {
    var highlight = function (element, text) {
        // Clear all highlights
        var highlightRegex = /(<span class="highlight">)([^<>]*)(<\/span>)/gi;
        element.innerHTML = element.innerHTML.replace(highlightRegex, "$2");

        if (!text) { return }

        var regex = new RegExp("(" + text + ")", "gi");
        var highlight = '<span class="highlight">$1</span>';

        // Highlight matches
        var traverseElement = function (element) {
            var elementHTML = "";
            var nodes = element.childNodes;
            for (var i = 0; i < nodes.length; i++) {
                var node = nodes[i];
                if (node.nodeType === 3) {
                    elementHTML += node.textContent.replace(regex, highlight);
                } else {
                    if (node.childNodes.length) {
                        traverseElement(node);
                    }
                    elementHTML += node.outerHTML;
                }
            }
            element.innerHTML = elementHTML;
        };

        traverseElement(element);
    };

    $.fn.highlite = function (options) {
        return this.each(function () {
            var element = this;
            var content = element.textContent;
            var text = options.text;
            var regex = new RegExp(text, "gi");
            var match = Boolean(content.match(regex));
            if (match) {
                highlight(element, text);
            }
        });
    };
})(jQuery, window, document);
