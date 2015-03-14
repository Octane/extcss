<table border='0'><tr><td align='center'>
<h2>Converting</h2>
</td></tr><tr><td valign='top'>
<h3>from Ext CSS</h3>
<pre><code>.post {<br>
	font-size: 0.875em;<br>
	p, ul, ol {<br>
		margin: 0 0 14px;<br>
	}<br>
	ul, ol {<br>
		padding: 0;<br>
		li {<br>
			display: list-item;<br>
		}<br>
	}<br>
}<br>
</code></pre>
</td><td valign='top'>
<h3>to simple CSS</h3>
<pre><code>.post {<br>
	font-size: 0.875em;<br>
}<br>
.post p, .post ul, .post ol {<br>
	margin: 0 0 14px;<br>
}<br>
.post ul, .post ol {<br>
	padding: 0;<br>
}<br>
.post ul li, .post ol li {<br>
	display: list-item;<br>
}<br>
</code></pre>
</td></tr><tr><td align='center'>
<h2>Advantages</h2>
</td></tr><tr><td valign='top'>
<ul><li>Fast and lightweight<br>
</li><li>Uses PHP for greater compatibility<br>
</li><li>Support CSS Variables<br>
</li><li>Cross-browser valid code<br>
</td></tr></table>