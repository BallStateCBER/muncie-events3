<?php 
// This element has $available_tags and (optionally) $selected_tags passed into it

// Counters CakePHP's variable-renaming weirdness
if (! isset($available_tags)) {
	$available_tags = isset($availableTags) ? $availableTags : array();
}
if (! isset($selected_tags)) {
	$selected_tags = isset($selectedTags) ? $selectedTags : array();
}
if (! isset($hide_label)) {
	$hide_label = false;
}
?>

<div class="input">
	<table id="tag_editing">
		<thead>
			<tr>
				<th>
					<label style="float: left;">
						<?php if (! $hide_label): ?>
							Tags
						<?php endif; ?>
					</label>
					<span>Available</span>
				</th>
				<td class="tween_spacer">
					&nbsp;
				</td>
				<th>
					<span>Selected</span>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td id="available_tags" class="fake_input">
				</td>
				<td class="tween_spacer">
					<img src="/img/icons/arrow_right.png" title="Selected tags appear over here." alt="&rarr;" />
				</td>
				<td id="selected_tags" class="fake_input">
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td>
					<?php if ($allow_custom): ?>
						&nbsp;<br />
						<label style="height: 20px;">
							Additional Tags
							<span id="tag_autosuggest_loading" style="display: none;">
								<img src="/img/loading_small.gif" alt="Working..." title="Working..." style="vertical-align:top;" />
							</span>
						</label>
						<?php 
							echo $this->Form->input('custom_tags', array(
								'label' => false, 
								'style' => 'margin-right: 5px; width: 100%; display: block;', 
								'between' => '<div class="footnote">Write out tags, separated by commas</div>',
								'id' => 'custom_tag_input'
							)); 
						?>
						<?php $this->Js->buffer("setupCustomTagInput();"); ?>
						<span class="footnote">
							<a href="#" id="new_tag_rules_toggler">Rules for creating new tags</a>
						</span>
						<div id="new_tag_rules" style="display: none;">
							<p>
								Before entering new tags, please search for existing tags that meet your needs.
								Once you start typing, please select any appropriate suggestions that appear below the input field.
								Doing this will make it more likely that your event will be linked to popular tags that are viewed by more visitors.
							</p>
							
							<p>
								New tags must:
							</p>
							<ul>
								<li>
									be short, general descriptions that people might search for, describing what will take place at the event
								</li>
								<li>
									be general enough to also apply to other events (including events outside of a series)
								</li>
							</ul>
							
							<p>
								Must not:
							</p>
							<ul>
								<li>
									include punctuation, such as dashes, commas, slashes, periods, etc.
								</li>
								<li>
									include profanity, email addresses, or website addresses
								</li>
								<li>
									be the name of the location (having this as a tag would be redundant, since people can already view events by location)
								</li>
							</ul>
						</div>
						<?php $this->Js->buffer("
							$('#new_tag_rules_toggler').click(function(event) {
								event.preventDefault();
								$('#new_tag_rules').slideToggle(200);
							});
						"); ?>
					<?php endif; ?>
				</td>
				<td class="tween_spacer">
					&nbsp;
				</td>
				<td style="vertical-align: top;">
					<a href="#" id="tag_help_toggler">
						<img src="/img/icons/help.png" alt="Help" title="Help" class="help_toggler" />
					</a>
					<div id="tag_help" class="footnote" style="display: none;">
						<ul style="margin: 0px; padding-left: 15px;">
							<li>Click the [+] button to the right of tags to add them.</li>
							<li>After a tag has been added, click the [-] button to the right of it to remove it.</li> 
							<li>Some tags can be clicked on to expand them and see related tags.</li>
							<li>Some tags, like the ones that are just headers for larger categories, can't be selected.</li>
						</ul>
					</div>
					<?php $this->Js->buffer("
						$('#tag_help_toggler').click(function (event) {
							event.preventDefault();
							$('#tag_help').fadeToggle(300);
						});
					"); ?>
				</td>
			</tr>
		</tfoot>
	</table>
</div>
<?php echo $this->Tag->setup($available_tags, 'available_tags', $selected_tags); ?>