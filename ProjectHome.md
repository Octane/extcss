<table border='0'><tr><td align='center'>
<h2>Converting</h2>
</td></tr><tr><td valign='top'>
<h3>from Ext CSS</h3>
<pre><code>.post {
	font-size: 0.875em;
	p, ul, ol {
		margin: 0 0 14px;
	}
	ul, ol {
		padding: 0;
		li {
			display: list-item;
		}
	}
}
</code></pre>
</td><td valign='top'>
<h3>to simple CSS</h3>
<pre><code>.post {
	font-size: 0.875em;
}
.post p, .post ul, .post ol {
	margin: 0 0 14px;
}
.post ul, .post ol {
	padding: 0;
}
.post ul li, .post ol li {
	display: list-item;
}
</code></pre>
</td></tr><tr><td align='center'>
<h2>Advantages</h2>
</td></tr><tr><td valign='top'>
<ul><li>Fast and lightweight<br>
</li><li>Uses PHP for greater compatibility<br>
</li><li>Support CSS Variables<br>
</li><li>Cross-browser valid code<br>
</td></tr></table>