<?php

/**
 * Tophat
 * 
 * Tophat is an inherited view loader for CodeIgniter. It works much the same way Ruby on Rails
 * handles template inheritance while folding in some common CodeIgniter syntax.
 * 
 * It should work as a drop in replacement for CodeIgniter views with additional power coming
 * through the `extend` and `content_for` methods.
 * 
 */
class Tophat
{
	
	/**
	 * Contexts
	 * 
	 * Keeps track of template levels through an array of "contexts". Each context is an instance
	 * of the Tophat_context object. The array is ordered with the deepest template first and the
	 * highest template last.
	 */
	private $contexts = array();
	
	/**
	 * Constructor
	 * 
	 * Called when CI loads and provides us access to the CI super object.
	 */
	public function __construct()
	{
		$this->CI =& get_instance();
	}
	
	/**
	 * Load
	 * 
	 * Similar to the CI loader accepts three variables,
	 * 
	 * * The template to load
	 * * Any variables to parse
	 * * Whether this function should return or render
	 */
	public function load($template, $blocks=array(), $should_return=FALSE)
	{
		$this->contexts[] = ($context = new Tophat_context($template, $blocks));
		return $context->render($should_return);
	}
	
	/**
	 * Context
	 * 
	 * Returns the currently active context (the top) off the contexts array.
	 */
	public function context()
	{
		return $this->contexts[count($this->contexts)-1];
	}
	
}

/**
 * Context
 * 
 * The Context object keeps track of each "level" of template and stores any template blocks in
 * local variables. Essentially, each time you use `extend` you create another Tophat_context.
 */
class Tophat_context
{
	
	/**
	 * Template
	 * 
	 * The name of the template to render
	 */
	private $template;
	
	/**
	 * Extends
	 * 
	 * Keeps track of whether this context/template is extending another template or if we're at
	 * the top of the chain.
	 */
	private $extends = FALSE;
	
	/**
	 * Blocks
	 * 
	 * In CI this is the variable array you pass to $ci->load->view(). In Tophat syntax it's simply
	 * referred to as "blocks".
	 */
	private $blocks = array();
	
	/**
	 * Block Names
	 * 
	 * As our parser goes through we only really want people to have to define block names at the
	 * beginning of the block. This array will grow and shrink as templates are parsed to keep
	 * track of open blocks. It is used by the `tophat_helper` file.
	 */
	public $block_names = array();
	
	/**
	 * Constructor
	 * 
	 * Similar to the CI view loader accepts the template to render any any associated variables
	 * to apply to the local scope.
	 */
	public function __construct($template, $blocks=array())
	{
		$this->CI =& get_instance();
		$this->template = $template;
		$this->blocks = $blocks;
	}
	
	/**
	 * Set Extends
	 * 
	 * If a template calls `extend` this method get's run and stores what template we should
	 * be extending.
	 */
	public function set_extends($template)
	{
		$this->extends = $template;
	}
	
	/**
	 * Set Block
	 * 
	 * Stores block content for the current context in the class.
	 */
	public function set_block($block_name, $block_content)
	{
		$this->blocks[$block_name] = $block_content;
	}
	
	/**
	 * Get Block
	 * 
	 * Returns the block content from the current context.
	 */
	public function get_block($block_name)
	{
		return @$this->blocks[$block_name];
	}
	
	/**
	 * Render
	 * 
	 * Kicks off rendering of this context, this is the actual loop. In here, if `extend` is called
	 * we'll create a new context and start all over again until no more extends are found.
	 */
	public function render($should_return)
	{
		$block = $this->CI->load->view($this->template, $this->blocks, TRUE);
		
		// @TODO Consider putting some template libraries in here, right here. After the block
		// comes back parse it for Mustache tags using $this->blocks as the variables.
		
		if ($this->extends)
		{
			$this->blocks['yield'] = $block;
			return $this->CI->tophat->load($this->extends, $this->blocks, $should_return);
		}
		
		else
		{
			if ($should_return)
			{
				return $block;
			}
			
			else
			{
				$this->CI->output->append_output($block);
			}
		}
	}
	
}