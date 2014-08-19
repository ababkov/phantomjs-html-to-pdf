<?php

namespace Rex\PhantomJs;

use Rex\PhantomJs\Common\Exception\Exception;
use Rex\PhantomJs\Common\Exception\InvalidArgumentException;
use Rex\PhantomJs\Common\Exception\RuntimeException;
use Symfony\Component\Process;

class Renderer{
	protected $_bin_path = null;
	protected $_html_uri = "";
	protected $_html_local_uri = "";

	protected $_options = array(
		Constants::OPTION_FORMAT=>Constants::FORMAT_A4,
		Constants::OPTION_MARGIN_LEFT=>"1cm",
		Constants::OPTION_MARGIN_RIGHT=>"1cm",
		Constants::OPTION_MARGIN_TOP=>"1cm",
		Constants::OPTION_MARGIN_BOTTOM=>"1cm",
		Constants::OPTION_MARGIN=>"",
		Constants::OPTION_ORIENTATION=>Constants::ORIENTATION_PORTRAIT,
		Constants::OPTION_ZOOM=>1,
		Constants::OPTION_FOOTER_HTML_PATH=>null,
		Constants::OPTION_FOOTER_HEIGHT=>"0cm",
		Constants::OPTION_HEADER_HTML_PATH=>null,
		Constants::OPTION_HEADER_HEIGHT=>"0cm",
		Constants::OPTION_WAIT_TIME=>200,
		Constants::OPTION_HEADER_ON_FIRST_PAGE=>false,
		Constants::OPTION_FOOTER_ON_FIRST_PAGE=>true
	);

	/**
	 * Construct a new instance of the class
	 * @param array $option_map A key=>value map of valid options. Call describeOptions for more info.
	 * @param null $bin_path
	 */
	public function __construct($option_map=array(),$bin_path=null){
		if( $option_map )
			$this->setOptions($option_map);
		$this->setBinPath($bin_path);
	}

	/**
	 * Describe the options that can be set via setOption / getOption
	 * @return array
	 */
	public function describeOptions(){
		return array(
			Constants::OPTION_FORMAT=>"The page format e.g. 'A4', '10cm*20cm' or any of the Constants::FORMAT_* constants",
			Constants::OPTION_MARGIN_LEFT=>"The left margin as an int / float + a unit. E.g. 1cm or 1.1in",
			Constants::OPTION_MARGIN_RIGHT=>"The right margin as an int / float + a unit. E.g. 1cm or 1.1in",
			Constants::OPTION_MARGIN_TOP=>"The top margin as an int / float + a unit. E.g. 1cm or 1.1in",
			Constants::OPTION_MARGIN_BOTTOM=>"The bottom margin as an int / float + a unit. E.g. 1cm or 1.1in",
			Constants::OPTION_ORIENTATION=>"The orientation: Constants::ORIENTATION_PORTRAIT, Constants::ORIENTATION_LANDSCAPE",
			Constants::OPTION_ZOOM=>"The zoom level where 1 is 100%. e.g. for 140% use 1.4",
			Constants::OPTION_HEADER_HTML=>"An html string to be used as the header. Use {{page_number}} for the page number, {{total_pages}} for the total pages. Ensure you also set the header height option.",
			Constants::OPTION_FOOTER_HTML=>"An html string to be used as the footer. Use {{page_number}} for the page number, {{total_pages}} for the total pages. Ensure you also set the footer height option.",
			Constants::OPTION_HEADER_HEIGHT=>"The height of the footer as an int / float + a unit. E.g. 1cm or 1.1in",
			Constants::OPTION_FOOTER_HEIGHT=>"The height of the header as an int / float + a unit. E.g. 1cm or 1.1in",
			Constants::OPTION_WAIT_TIME=>"The wait time in ms",
			Constants::OPTION_HEADER_ON_FIRST_PAGE=>"True if the header should be included on first page",
			Constants::OPTION_FOOTER_ON_FIRST_PAGE=>"True if the footer should be included on first page",
		);
	}

	/**
	 * Set the path to the phantom js binary. If not set, will assume the exe / bin is in the system path
	 * @param string $path The full path to the binary including the binary name
	 * @throws Common\Exception\InvalidArgumentException
	 * @return void
	 */
	public function setBinPath($path=null){
		if( $path && !file_exists($path) )
			throw new InvalidArgumentException("The phantom js binary couldn't be found at the path you specified '{$path}'");

		$this->_bin_path = $path;
	}

	/**
	 * Get the path to the phantom js binary
	 */
	public function getBinPath(){
		return $this->_bin_path;
	}

	/**
	 * Specify the local file path or url to render
	 * @param string $uri The uri to render
	 * @throws Common\Exception\Exception
	 * @return void
	 */
	public function setHtmlContentFromUri($uri){
		//Clear any existing local file
		if( $this->_html_local_uri ){
			unlink($this->_html_local_uri);
		}

		//Return if nothing set
		if( !$uri ){
			return;
		}

		//Fetch content to local file
		$src_h = fopen($uri,'r');
		if( $src_h === false )
			throw new Exception("Content could not be fetched from '{$uri}'");
		$this->_html_local_uri = $this->_getNewTempFilePath("html");
		$target_h = fopen($this->_html_local_uri,"w+");
		if( $target_h === false )
			throw new Exception("File could not be created at '{$this->_html_local_uri}'");
		stream_copy_to_stream($src_h,$target_h);
		fclose($src_h);
		fclose($target_h);
	}

	/**
	 * Set the html content to render directly rather than relying on a temporary file or url
	 * @param string $content The content to set
	 */
	public function setHtmlContent($content){
		$this->_html_local_uri = $this->_getNewTempFilePath("html");
		file_put_contents($this->_html_local_uri,$content);
	}

	/**
	 * Get the html content that is to be rendered
	 * @throws Common\Exception\Exception
	 * @return string
	 */
	public function getHtmlContent(){
		if( !$this->_html_local_uri )
			throw new Exception("Html content hasn't been set yet");
		return file_get_contents($this->_html_local_uri);
	}

	/**
	 * Set multiple options at once
	 * @param array $option_map A key=>value map of options
	 * @return void
	 */
	public function setOptions($option_map){
		foreach($option_map as $option_key=>$option_value){
			$this->setOption($option_key,$option_value);
		}
	}

	/**
	 * Set a single option
	 * @param string $option_key Set an option (call describeOptions for more information on each option)
	 * @param string $option_value The value to set (call describeOptions for more information on each option)
	 * @throws Common\Exception\InvalidArgumentException
	 * @return void
	 */
	public function setOption($option_key,$option_value){
		switch($option_key){
			case Constants::OPTION_ZOOM:
				if( filter_var($option_value,FILTER_VALIDATE_INT) === false && filter_var($option_value,FILTER_VALIDATE_FLOAT) === false && $option_value != null )
					throw new InvalidArgumentException("The '{$option_key}' option expects an integer or decimal. The value you specified '{$option_value}' is not a valid value.");
				break;
			case Constants::OPTION_MARGIN_LEFT:
			case Constants::OPTION_MARGIN_RIGHT:
			case Constants::OPTION_MARGIN_TOP:
			case Constants::OPTION_MARGIN_BOTTOM:
			case Constants::OPTION_MARGIN:
			case Constants::OPTION_FOOTER_HEIGHT:
			case Constants::OPTION_HEADER_HEIGHT:
				if( !preg_match("/\d+(\.\d+)?(mm|cm|in|px|em)/",$option_value) && $option_value !== null && $option_value != 0 )
					throw new InvalidArgumentException("The '{$option_key}' option expects an integer or decimal followed by a unit: cm,mm,em,in,px. The value you specified '{$option_value}' is invalid.");
				break;
			case Constants::OPTION_ORIENTATION:
				if( !in_array($option_value,array(Constants::ORIENTATION_PORTRAIT,Constants::ORIENTATION_LANDSCAPE)) )
					throw new InvalidArgumentException("The '{}' expects either '".Constants::ORIENTATION_PORTRAIT."' or '".Constants::ORIENTATION_LANDSCAPE."'. The value you specified '{$option_value}' is invalid.");
				break;
			case Constants::OPTION_FOOTER_HTML:
			case Constants::OPTION_HEADER_HTML:
				if( $option_key == Constants::OPTION_FOOTER_HTML )
					$file_option_key = Constants::OPTION_FOOTER_HTML_PATH;
				else
					$file_option_key = Constants::OPTION_HEADER_HTML_PATH;

				$file_option_value = $this->getOption($option_key);

				if( $file_option_value && $option_value===null ){
					unlink($file_option_value);
					unset($this->_options[$file_option_key]);
				} else {
					if( !file_exists($file_option_value) ){
						$file_option_value = $this->_getNewTempFilePath("html");
						$this->_options[$file_option_key] = $file_option_value;
					}
					file_put_contents($file_option_value,$option_value);
				}
				return;
				break;
			case Constants::OPTION_FOOTER_ON_FIRST_PAGE:
			case Constants::OPTION_HEADER_ON_FIRST_PAGE:
			case Constants::OPTION_FORMAT:
				//Anything is okay here
				break;
			default:
				throw new InvalidArgumentException("The option '{$option_key}' is not a valid option. Valid options include ".implode(",",array_keys($this->_options)));
		}

		//If margin, let's set each individual margin key
		if( $option_key == Constants::OPTION_MARGIN ){
			$this->setOption(Constants::OPTION_MARGIN_TOP,$option_value);
			$this->setOption(Constants::OPTION_MARGIN_RIGHT,$option_value);
			$this->setOption(Constants::OPTION_MARGIN_BOTTOM,$option_value);
			$this->setOption(Constants::OPTION_MARGIN_LEFT,$option_value);
		}

		$this->_options[$option_key] = $option_value;
	}

	/**
	 * Get defined options
	 * @return array An array of options
	 */
	public function getOptions(){
		return $this->_options;
	}

	/**
	 * Get single defined option
	 * @param string $option_key The option key
	 * @return null
	 */
	public function getOption($option_key){
		if( !isset($this->_options[$option_key]) )
			return null;
		return $this->_options[$option_key];
	}

	/**
	 * Output the file to a specified path or to a temp path
	 * @param string $output_path If not provided, the file will be saved somewhere in the temp directory of the system
	 * @throws Common\Exception\RuntimeException
	 * @return string The final path where the file was saved
	 */
	public function save($output_path=null){
		//Output path - set to a temp file if not
		if( !$output_path )
			$output_path = $this->_getNewTempFilePath("pdf");

		//Build process
		$builder = new Process\ProcessBuilder(array(
			dirname(__FILE__)."/Js/render.js",
			$this->_html_local_uri,
			$output_path,
			json_encode($this->_options)
		));
		$builder->setPrefix($this->getBinPath()?$this->getBinPath():"phantomjs");
		$process = $builder->getProcess();
		//Run process
		$process->run();

		if( !$process->isSuccessful() )
			throw new RuntimeException($process->getOutput()." ".$process->getErrorOutput().", Command Line: ".$process->getCommandLine());

		//Return the output path
		return $output_path;
	}

	/**
	 * Creates a new temp file with an extension
	 * @param string $extension The extension to add to the end of the file
	 * @return string
	 */
	protected function _getNewTempFilePath($extension="tmp"){
		$temp_file_name = tempnam(sys_get_temp_dir(),"renderer").".{$extension}";
		if( file_exists($temp_file_name) )
			return $this->_getNewTempFilePath($extension);
		return $temp_file_name;
	}
}