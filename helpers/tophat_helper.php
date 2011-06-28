<?php

function extend($template)
{
	$CI =& get_instance();
	$CI->tophat->context()->set_extends($template);
}

function content_for($block_name = FALSE, $should_return = FALSE)
{
	$CI =& get_instance();
	$block_content = $CI->tophat->context()->get_block($block_name ? $block_name : 'yield');
	
	if ($should_return)
	{
		return $block_content;
	}
	
	echo $block_content;
}

function begin_content_for($block_name)
{
	$CI =& get_instance();
	$CI->tophat->context()->block_names[] = $block_name;
	
	ob_start();
}

function end_content()
{
	end_content_for();
}

function end_content_for($block_name = FALSE)
{
	$block_content = ob_get_contents();
	ob_end_clean();
	
	$CI =& get_instance();
	
	if ($block_name == FALSE)
	{
		$block_name = array_pop($CI->tophat->context()->block_names);
	}
	
	$CI->tophat->context()->set_block($block_name, $block_content);
}

function yield($block_name = FALSE, $should_return = FALSE)
{
	return content_for($block_name, $should_return);
}