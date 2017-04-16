<?php
	$data = array();
	foreach ($nodes as $node){
		$text = ucwords($node['Tag']['name']).' ('.$node['Tag']['id'].')';
		if (! $node['Tag']['selectable']) {
			$text = '<span style="color: blue;">'.$text.'</span>';
		} elseif ($showNoEvents && isset($node['Tag']['no_events']) && $node['Tag']['no_events']) {
			$text = '<span style="color: red;">'.$text.'</span>';
		}
		$datum = array(
			"text" => $text, 
			"id" => $node['Tag']['id'], 
			"cls" => "folder",
			"leaf" => ($node['Tag']['lft'] + 1 == $node['Tag']['rght'])
		);
		/* The 'Delete' group needs to be available to drag tags into,
		 * but if it's emptied, it becomes a leaf. Here, that's prevented. */
		if (strtolower($node['Tag']['name']) == 'delete') {
			$datum['leaf'] = false;	
		}
		if (isset($_GET['no_leaves'])) {
			$datum['leaf'] = false;
		}
		$data[] = $datum;
	}
	echo $this->Js->object($data);