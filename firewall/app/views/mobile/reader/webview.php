<div id="webview">
	<div class="header">
		<div class="left">
			<a onclick="Fever.iPhone.unloadWebView();" class="btn back">Back</a>
		</div>

		<div class="title">
			Elsewhere
		</div>

		<div class="right">
			<a id="safari" class="btn safari">Open in Safari</a>
		</div>
	</div>

	<iframe id="iframe" src="about:blank" onload="Fever.iPhone.onIframeLoaded();"></iframe>
</div>