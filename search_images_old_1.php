<div id="content"></div>
<script>
    function hndlr(response) {
        for (var i = 0; i < response.items.length; i++) {
            var item = response.items[i];
            // Make sure HTML in item.htmlTitle is escaped.
            document.getElementById("content").append(
                document.createElement("br"),
                document.createTextNode(item.htmlTitle)
            );
        }
    }
</script>
<script src="https://www.googleapis.com/customsearch/v1?key=AIzaSyDUdTU4wrmcy5EvTXcg8M3yXJerut_nT5A&cx=017576662512468239146:omuauf_lfve&q=cars&callback=hndlr">
</script>