<h2 class="heading">Link Optimization</h2>
<div class="useo-option-container">
	<div class="useo-option-header-content">
		Using Criteria in this section you can change the relation of links to certain sites.
		It's very useful to set friendly sites (or even this site) to "DoFollow" to pass them SEO Juice.
		Take into account the domain google.com is different from www.google.com
	</div>
	<div class="useo-option-header-content">
		<input type="button" value="Add domain" onclick="addNetworkSite()" class="useo-btn useo-right">
	</div>
	<div id="useo-link-criteria" class="useo-link-criteria">
	</div>
</div>
<?php
$relations = json_decode(qa_opt('useo_link_relations'),true);
if (is_array($relations)) {
	foreach($relations as $key => $value){
		$rel[$value['rel']] = 'selected=""';
		?>
		<div class="useo-option-container" id="useo-criteria-container">
			<div class="useo-option-detail">Domain:</div>
			<div class="useo-option-content">
				<div class="useo-text-container">
					<input value="<?php echo @$value['url']; ?>" type="text" name="useo_link_url[]" placeholder="google.com or www.google.com" class="useo-text" id="">
				</div>
			<div class="useo-list-container">
				<select name="useo_link_rel[]" class="useo-list">
					<option <?php echo @$rel[1]; ?> value="1">Nofollow</option>
					<option <?php echo @$rel[2]; ?> value="2">External</option>
					<option <?php echo @$rel[3]; ?> value="3">Nofollow External</option>
					<option <?php echo @$rel[4]; ?> value="4">Dofollow (no "rel" attribute)</option>
				</select>
			</div>
			<div class="useo-button-container">
				<input type="button" value="Remove domain" onclick="remove_link_domain(this)"></div>
			</div>
		</div>
		<?php
		$rel[$value['rel']] = '';
	}
}
?>
