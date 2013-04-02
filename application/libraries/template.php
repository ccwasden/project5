<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Template
{
	public function page($name,$data=NULL)
	{
	    $CI =& get_instance();

	    $CI->load->view('fragments/header_view',$data);
	    $CI->load->view($name,$data);
	    $CI->load->view('fragments/footer_view');        
	}
}