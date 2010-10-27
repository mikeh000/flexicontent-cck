<?php
/**
 * @version 1.5 stable $Id: item.php 250 2010-06-09 08:01:27Z emmanuel.danan $
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 * 
 * FLEXIcontent is a derivative work of the excellent QuickFAQ component
 * @copyright (C) 2008 Christoph Lukes
 * see www.schlu.net for more information
 *
 * FLEXIcontent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
// first define the template name
$tmpl = $this->tmpl;
?>

<div id="flexicontent" class="flexicontent item<?php echo $this->item->id; ?> type<?php echo $this->item->type_id; ?>">

<!-- BOF buttons -->
<p class="buttons">
	<?php echo flexicontent_html::pdfbutton( $this->item, $this->params ); ?>
	<?php echo flexicontent_html::mailbutton( 'items', $this->params, null , $this->item->slug ); ?>
	<?php echo flexicontent_html::printbutton( $this->print_link, $this->params ); ?>
	<?php echo flexicontent_html::editbutton( $this->item, $this->params ); ?>
</p>
<!-- EOF buttons -->

<!-- BOF page title -->
<?php if ($this->params->get( 'show_page_title', 1 ) && $this->params->get('page_title') != $this->item->title) : ?>
<h1 class="componentheading">
	<?php echo $this->params->get('page_title'); ?>
</h1>
<?php endif; ?>
<!-- EOF page title -->

<!-- BOF item title -->
<?php if ($this->params->get('show_title', 1)) : ?>
<h2 class="contentheading flexicontent">
	<?php echo $this->escape($this->item->title); ?>
</h2>
<?php endif; ?>
<!-- EOF item title -->

<!-- BOF subtitle1 block -->
<?php if (isset($this->item->positions['subtitle1'])) : ?>
<div class="lineinfo subtitle1">
	<?php foreach ($this->item->positions['subtitle1'] as $field) : ?>
	<span class="element">
		<?php if ($field->label) : ?>
		<span class="label field_<?php echo $field->name; ?>"><?php echo $field->label; ?></span>
		<?php endif; ?>
		<span class="value field_<?php echo $field->name; ?>"><?php echo $field->display; ?></span>
	</span>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<!-- EOF subtitle1 block -->

<!-- BOF subtitle2 block -->
<?php if (isset($this->item->positions['subtitle2'])) : ?>
<div class="lineinfo subtitle2">
	<?php foreach ($this->item->positions['subtitle2'] as $field) : ?>
	<span class="element">
		<?php if ($field->label) : ?>
		<span class="label field_<?php echo $field->name; ?>"><?php echo $field->label; ?></span>
		<?php endif; ?>
		<span class="value field_<?php echo $field->name; ?>"><?php echo $field->display; ?></span>
	</span>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<!-- EOF subtitle2 block -->

<!-- BOF subtitle3 block -->
<?php if (isset($this->item->positions['subtitle3'])) : ?>
<div class="lineinfo subtitle3">
	<?php foreach ($this->item->positions['subtitle3'] as $field) : ?>
	<span class="element">
		<?php if ($field->label) : ?>
		<span class="label field_<?php echo $field->name; ?>"><?php echo $field->label; ?></span>
		<?php endif; ?>
		<span class="value field_<?php echo $field->name; ?>"><?php echo $field->display; ?></span>
	</span>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<!-- EOF subtitle3 block -->


<?php if ((isset($this->item->positions['image'])) || (isset($this->item->positions['top']))) : ?>
<div class="topblock">
<!-- BOF image block -->
	<?php if (isset($this->item->positions['image'])) : ?>
		<?php foreach ($this->item->positions['image'] as $field) : ?>
	<div class="image field_<?php echo $field->name; ?>">
		<?php echo $field->display; ?>
		<div class="clear"></div>
	</div>
		<?php endforeach; ?>
	<?php endif; ?>
<!-- EOF image block -->

	<?php if (isset($this->item->positions['top'])) : ?>
<!-- BOF top block -->
	<div class="infoblock <?php echo $this->params->get('top_cols', 'two'); ?>cols">
		<ul>
			<?php foreach ($this->item->positions['top'] as $field) : ?>
			<li>
				<div>
					<?php if ($field->label) : ?>
					<div class="label field_<?php echo $field->name; ?>"><?php echo $field->label; ?></div>
					<?php endif; ?>
					<div class="value field_<?php echo $field->name; ?>"><?php echo $field->display; ?></div>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
<!-- EOF top block -->
	<?php endif; ?>
</div>
<?php endif; ?>

	<div class="clear"></div>

	<?php if (isset($this->item->positions['description'])) : ?>
<!-- BOF description -->
	<div class="description">
		<?php foreach ($this->item->positions['description'] as $field) : ?>
			<?php if ($field->label) : ?>
		<div class="desc-title"><?php echo $field->label; ?></div>
			<?php endif; ?>
		<div class="desc-content"><?php echo $field->display; ?></div>
		<?php endforeach; ?>
	</div>
<!-- EOF description -->
	<?php endif; ?>

	<div class="clear"></div>

	<?php if (isset($this->item->positions['bottom'])) : ?>
<!-- BOF bottom block -->
	<div class="infoblock <?php echo $this->params->get('bottom_cols', 'two'); ?>cols">
		<ul>
			<?php foreach ($this->item->positions['bottom'] as $field) : ?>
			<li>
				<div>
					<?php if ($field->label) : ?>
					<div class="label field_<?php echo $field->name; ?>"><?php echo $field->label; ?></div>
					<?php endif; ?>
					<div class="value field_<?php echo $field->name; ?>"><?php echo $field->display; ?></div>
				</div>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
<!-- EOF bottom block -->
	<?php endif; ?>

	<?php if ($this->params->get('comments')) : ?>
<!-- BOF comments -->
	<div class="comments">
		<?php
		if ($this->params->get('comments') == 1) :
			if (file_exists(JPATH_SITE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php')) :
				require_once(JPATH_SITE.DS.'components'.DS.'com_jcomments'.DS.'jcomments.php');
				echo JComments::showComments($this->item->id, 'com_flexicontent', $this->escape($this->item->title));
			endif;
		endif;
	
		if ($this->params->get('comments') == 2) :
			if (file_exists(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'jom_comment_bot.php')) :
    			require_once(JPATH_SITE.DS.'plugins'.DS.'content'.DS.'jom_comment_bot.php');
    			echo jomcomment($this->item->id, 'com_flexicontent');
  			endif;
  		endif;
		?>
	</div>
<!-- EOF comments -->
	<?php endif; ?>

</div>