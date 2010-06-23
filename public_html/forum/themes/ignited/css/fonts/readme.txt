Font files (as in web fonts) should be placed in this folder for your theme.

Fonts for use on the web should normally come in three formats;
1) EOT - Used for MSIE
2) OTF - Used for Webkit based browsers and Opera.
3) WOFF - Used for Mozilla based browsers. May be used by MSIE 9.

In CSS files which untilize web fonts, the (current, cross-browser) safest way to do so is as so;

@font-face {
	font-family: '<Name of font you want to use>';
	src: url('../fonts/<font file name WITHOUT extension>');
}

Browsers fetch the file they accept as a web font automatically if the extension is omitted.

You should ALWAYS have backup, normal fonts in place of a web font.