<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

img {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}
a img {border:none;}
p {margin: 1em 0;}
h1, h2, h3, h4, h5, h6 {color: black !important;}
h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: blue !important;}
table td {
	border-collapse: collapse;
	overflow: hidden;
	page-break-inside: avoid;
}

table tr {
	border-collapse: collapse;
}

table {
	border-collapse: collapse;
	border-spacing: 0;
	page-break-inside: avoid;
	border: 0;
	margin: 0;
	padding: 0;
	overflow: wrap;
}
@page {
	margin-top: 1cm;
	margin-bottom: 1cm;
	margin-left: 2cm;
	margin-right: 2cm;
}
body {
	width:100% !important;
	background: #fff;
	color: #000;
	font-size: 14px;
	line-height: 100%;
	-webkit-text-size-adjust:100%;
	-ms-text-size-adjust:100%;
	margin:0;
	padding:0;
}

.label {
	color: <?php echo esc_html( $color_label_ticket ); ?>;
}
.content {
	color: <?php echo esc_html( $color_content_ticket ); ?>;
}
.border {
	border: 5px solid <?php echo esc_html( $color_border_ticket ); ?>;
	border-collapse: collapse;
}
.border-none {
	border: 0;
}
.padding {
	padding: 15px;
}
.border-top {
	border-top-width: 4px;
	border-color: <?php echo esc_html( $color_border_ticket ); ?>;
	border-style: solid;
}
.border-right {
	border-right-width: 4px;
	border-color: <?php echo esc_html( $color_border_ticket ); ?>;
	border-style: solid;
}
.border-bottom {
	border-bottom-width: 4px;
	border-color: <?php echo esc_html( $color_border_ticket ); ?>;
	border-style: solid;
}
.border-left {
	border-left-width: 4px;
	border-color: <?php echo esc_html( $color_border_ticket ); ?>;
	border-style: solid;
}