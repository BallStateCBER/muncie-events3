<?php 
	echo $content_for_layout;
	$this->Js->buffer("FB.XFBML.parse();");
	echo $this->Js->writeBuffer();