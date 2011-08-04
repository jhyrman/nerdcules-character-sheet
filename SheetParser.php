<?php
/**
 *  Parses an XML character sheet into an HTML representation
 *  @author Joshua D. Hyrman <jhyrman@gmail.com>
 *  @copyright Copyright (c) 2011 Joshua D. Hyrman. Distributed under the MIT License.
 *  @version 1.0.1
 *  @package Nerdcules.SheetManager
 */

/** @license
 *  Released under the MIT License:
 *********************************
 *  Permission is hereby granted, free of charge, to any person obtaining a copy of this software and 
 *  associated documentation files (the "Software"), to deal in the Software without restriction,
 *  including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 *  and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 *  subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 *  INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 *  IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 *  OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *********************************
 */
 
define( "__PARSER_DEBUG__", FALSE );
 
class SheetParser
{
	/* Buffers */
	/** holds the XML */
	private $xml = "";
	/** holds the XML tree */
	private $values = array();
	/** holds the HTML buffer */
	private $html = "";
	/** holds the style buffer */
	private $style = "";
	/** holds the custom script buffer */
	private $script = "";
	/** holds the script buffer for the form */
	private $scriptForm = "";
	/** holds the script buffer for the Elements */
	private $scriptElements = "";
	
	/* State Trackers */
	/** the current open tag */
	private $openTag = "";
	/** the parent tag, if we descend deeper */
	private $parentTag = "";
	/** keeps track of certain elements */
	private $currentTag = "";
	/** is this element a part of a row */
	private $inRow = false;
	/** keeps track of how many elements have been rendered */
	private $tabIndex = 0;
	
	/* Descriptive Information */
	/** the name of the system this is for */
	private $system = "";
	/** the name of the game this is for */
	private $game = "";
	/** the name of this sheet */
	private $name = "";
	/** the original copyright holder/publisher */
	private $copyright = "";
	/** the author who wrote the sheet for this parser */
	private $author = "";
	/** a description of the page; used for crawler results */
	private $description = "";
	
	/**
	 *  Converts spaces ( ) and dashes (-) to underscores (_)
	 *  @param string $original the original string to convert
	 *  @return string the converted string
	 */
	private function spaceToUnderscore( $original )
	{
		return ( string )str_replace( array( " ", "-" ), "_", $original );
	}
	
	/**
	 *  Appends a string to the HTML buffer
	 *  @param string $html the string to append
	 *  @return void
	 */
	private function appendHTML( $html )
	{
		$this->html .= $html;
	}
	
	/**
	 *  Appends a string to the form script buffer
	 *  @param string $script the script to append
	 *  @return void
	 */
	private function appendForm( $script )
	{
		$this->scriptForm .= $script;
	}
	
	/**
	 *  Appends a string to the elements script buffer
	 *  @param string $script the script to append
	 *  @return void
	 */
	private function appendElements( $script )
	{
		$this->scriptElements .= $script;
	}
	
	/**
	 *  Increment the element index
	 *  @return void
	 */
	private function incrementIndex()
	{
		$this->tabIndex += 1;
	}
	
	/**
	 *  Creates a label for the passed element. Called by an element function
	 *  @param array &$tag an array-representation of the current tag element
	 *  @return void
	 */
	private function appendLabel( &$tag )
	{
		//if the NAME attribute is set, we'll use that ( <select> )
		//otherwise, use the tag's value ( everything else )
		if ( isset( $tag['attributes']['NAME'] ) )
		{
			$id =& $tag['attributes']['NAME'];
		}
		else
		{
			$id =& $tag['value'];
		}
		
		//see what kind of label to print
		if ( strpos( $id, "{blank}" ) !== FALSE )
		{
			//do not print a label
			//get rid of the {blank}
			$id = str_replace( "{blank}", "", $id );
		}
		elseif ( strpos( $tag['value'], "{text}" ) !== FALSE )
		{
			//put a textbox for the name instead of a label
			//get rid of the {text}
			$id = str_replace( "{text}", "", $id );
			$this->appendHTML( "      <input type=\"text\" id=\"" . $this->spaceToUnderscore( $tag['value'] ) . "Name\"" );
			if ( isset( $tag['attributes']['SIZE'] ) )
			{
				$this->appendHTML( " size=\"{$tag['attributes']['SIZE']}\"" );
			}
			else
			{
				$this->appendHTML( " size=\"10\"" );
			}
			
			$this->appendHTML( " tabIndex=\"{$this->tabIndex}\" />\n" );
			
			//since we used this tabIndex, we need to increase it
			$this->incrementIndex();
			
			//create the script for this form element (name box)
			$this->appendForm( "      " . $this->spaceToUnderscore( $id ) . "Name: '" . $this->spaceToUnderscore( $id ) . "Name',\n" );
		}
		elseif ( strpos( $id, "{" ) !== FALSE )
		{
			//use a different label than the element name
			//use what's inside the brackets as the label, and what's outside as the name
			$label = substr( $id, strpos( $id, "{" ) + 1, strpos( $id, "}" ) - 1 );
			
			//get rid of the label inside the ID
			$id = str_replace( '{' . $label . '}', "", $id );
			
			$this->appendHTML( "      <label for=\"" . $this->spaceToUnderscore( $id ) . "\">{$label}</label>\n" );
		}
		else
		{
			//put the standard label
			$this->appendHTML( "      <label for=\"" . $this->spaceToUnderscore( $id ) . "\">{$id}</label>\n" );
		}
	}
	
	/**
	 *  Appends a line break ( <br /> ) to the HTML buffer if neccessary
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function appendBreak( $tag )
	{
		//if break=true, always append a break
		//if break=false, never append a break
		//if not inside a row, append a break
		//otherwise, do not append a break
		if ( isset( $tag['attributes']['BREAK'] ) )
		{
			if ( strtoupper( $tag['attributes']['BREAK'] ) === "TRUE" )
			{
				$this->appendHTML( "<br />\n" );
				return;
			}
			elseif ( strtoupper( $tag['attributes']['BREAK'] ) === "FALSE" )
			{
				return;
			}
			else
			{
				//invalid value for break
			}
		}
		
		if ( $this->inRow === FALSE )
		{
			$this->appendHTML( "<br />\n" );
			return;
		}
	}
	
	/**
	 *  Creates a new fieldset. Called by <set>
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function SetOpen( $tag )
	{
	
		$this->appendHTML(
					"<div class=\"fieldset\">\n" .
					"  <fieldset id=\"" . $this->spaceToUnderscore( $tag['attributes']['NAME'] ) . "\">\n" .
					"    <legend>{$tag['attributes']['NAME']}</legend>\n"
					);
	}
	
	/**
	 *  Closes the current fieldset. Called by </set>
	 *  @return void
	 */
	private function SetClose()
	{
		$this->appendHTML( "  </fieldset>\n</div>\n" );
	}
	
	/**
	 *  Creates a new column. Called by <column>
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function ColumnOpen( $tag )
	{
		$id = "";
		$name = "";
		
		if ( isset( $tag['attributes']['NAME'] ) )
		{
			$id = " id=\"" . $this->spaceToUnderscore( $tag['attributes']['NAME'] ) . "\"";
			$name = "      <strong>{$tag['attributes']['NAME']}</strong><br />\n";
		}
		
		$this->appendHTML( "    <div class=\"column\"{$id}>\n{$name}" );
	}
	
	/**
	 *  Closes the current column. Called by </column>
	 *  @return void
	 */
	private function ColumnClose()
	{
		$this->appendHTML( "    </div>\n" );
	}
	
	/**
	 *  Creates a text input field. Called by <text></text>
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function Textfield( $tag )
	{
		$this->incrementIndex();
		
		
		$this->appendLabel( $tag );
		$this->appendHTML( "      <input type=\"text\" id=\"" . $this->spaceToUnderscore( $tag['value'] ) . "\"" );
		
		if ( isset( $tag['attributes']['SIZE'] ) )
		{
			$this->appendHTML( " size=\"{$tag['attributes']['SIZE']}\"" );
		}
		else
		{
			$this->appendHTML( " size=\"10\"" );
		}
		
		$this->appendHTML( " tabIndex=\"{$this->tabIndex}\" onChange=\"Nerdcules.SheetChanged = true;\"/>" );
		$this->appendBreak( $tag );
	
		$this->appendForm( "      " . $this->spaceToUnderscore( $tag['value'] ) . ": '" . $this->spaceToUnderscore( $tag['value'] ) . "',\n" );
	}
	
	/**
	 *  Creates a new select field. Called by <select>
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function SelectOpen( $tag )
	{
		$this->incrementIndex();
		
		$this->appendHTML( $this->appendLabel( $tag ) );
		$this->appendHTML( "      <select id=\"" . $this->spaceToUnderscore( $tag['attributes']['NAME'] ) . "\" tabIndex=\"{$this->tabIndex}\">\n" );
		
		$this->appendForm( "      " . $this->spaceToUnderscore( $tag['attributes']['NAME'] ) . ": '" . $this->spaceToUnderscore( $tag['attributes']['NAME'] ) . "',\n" );
	
	}
	
	/**
	 *  Closes the current select field. Called by </select>
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function SelectClose( $tag )
	{
		$this->appendHTML( "      </select>" );
		$this->appendBreak( $tag );
	}
	
	/**
	 *  Creates an option element within a select field. Called by <option></option>
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function Option( $tag )
	{
		$this->appendHTML( "        <option value=\"" . $this->spaceToUnderscore( $tag['value'] ) . "\">{$tag['value']}</option>\n" );
	}
	
	/**
	 *  Creates a Nerdcules.Dots element. Called by <dot></dot>
	 *  Optional attributes are MIN=int (defaults to 0), MAX=int (defaults to 10), and INITIAL=int (defaults to MIN)
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function Dot( $tag )
	{
		$this->incrementIndex();
				
		$min = ( isset( $tag['attributes']['MIN'] ) ) ? $tag['attributes']['MIN'] : 0;
		$max = ( isset( $tag['attributes']['MAX'] ) ) ? $tag['attributes']['MAX'] : 10;
		$init = ( isset( $tag['attributes']['INITIAL'] ) ) ? $tag['attributes']['INITIAL'] : $min;
		
		$this->appendHTML( $this->appendLabel( $tag ) );
		$this->appendHTML( "      <span class=\"dot\" id=\"" . $this->spaceToUnderscore( $tag['value'] ) . "\" tabIndex=\"{$this->tabIndex}\" onkeydown=\"Nerdcules.CharacterSheet.Elements." . $this->spaceToUnderscore( $tag['value'] ) . ".keyPress( event )\"></span>\n" );
		$this->appendHTML( "      <span class=\"arrow\" onClick=\"Nerdcules.CharacterSheet.Elements." . $this->spaceToUnderscore( $tag['value'] ) . ".increment()\">&#8657;</span><span class=\"arrow\" onClick=\"Nerdcules.CharacterSheet.Elements." . $this->spaceToUnderscore( $tag['value'] ) . ".decrement()\">&#8659;</span>" );
		$this->appendBreak( $tag );
		
		$this->appendElements( "      " . $this->spaceToUnderscore( $tag['value'] ) . ": new Nerdcules.Dots( '" . $this->spaceToUnderscore( $tag['value'] ) . "', {$min},  {$max}, {$init}" );
		
		if ( isset( $tag['attributes']['FILLED'] ) )
		{
			$this->appendElements( ", '&#{$tag['attributes']['FILLED']};'" );
			
			if ( isset( $tag['attributes']['EMPTY'] ) )
			{
				$this->appendElements( ", '&#{$tag['attributes']['EMPTY']};'" );
			}
		}
		
		$this->appendElements( "),\n" );
	}
	
	/**
	 *  Creates a Nerdcules.Spinner element. Called by <spinner></spinner>
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function Spinner( $tag )
	{
		$this->incrementIndex();
		
		$min = ( isset( $tag['attributes']['MIN'] ) ) ? $tag['attributes']['MIN'] : 'unbounded';
		$max = ( isset( $tag['attributes']['MAX'] ) ) ? $tag['attributes']['MAX'] : 'unbounded';
		$init = ( isset( $tag['attributes']['INITIAL'] ) ) ? $tag['attributes']['INITIAL'] : 0;
		
		if ( $min === 'unbounded' )
		{
			$min = '"unbounded"';
		}
		
		if ( $max === 'unbounded' )
		{
			$max = '"unbounded"';
		}
		
		$this->appendHTML( $this->appendLabel( $tag ) );
		$this->appendHTML( "      <span class=\"spinner\" id=\"" . $this->spaceToUnderscore( $tag['value'] ) . "\" tabIndex=\"{$this->tabIndex}\" onkeydown=\"Nerdcules.CharacterSheet.Elements." . $this->spaceToUnderscore( $tag['value'] ) . ".keyPress( event )\"></span>\n" );
		$this->appendHTML( "      <span class=\"arrow\" onClick=\"Nerdcules.CharacterSheet.Elements." . $this->spaceToUnderscore( $tag['value'] ) . ".increment()\">&#8657;</span><span class=\"arrow\" onClick=\"Nerdcules.CharacterSheet.Elements." . $this->spaceToUnderscore( $tag['value'] ) . ".decrement()\">&#8659;</span>" );
		$this->appendBreak( $tag );
		
		$this->appendElements( "      " . $this->spaceToUnderscore( $tag['value'] ) . ": new Nerdcules.Spinner( '" . $this->spaceToUnderscore( $tag['value'] ) . "', {$min},  {$max}, {$init} ),\n" );
	}
	
	/**
	 *  Creates a checkbox element. Called by <check></check>
	 *  Optional attribute in BREAK=bool (defaults to true)
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function Check( $tag )
	{		
		$this->incrementIndex();
		$this->appendHTML( $this->appendLabel( $tag ) );
		$this->appendHTML( "      <input type=\"checkbox\" id=\"" . $this->spaceToUnderscore( $tag['value'] ) . "\" tabIndex=\"{$this->tabIndex}\" />" );
		$this->appendBreak( $tag );
		
		$this->appendForm( "      " . $this->spaceToUnderscore( $tag['value'] ) . ": '" . $this->spaceToUnderscore( $tag['value'] ) . "',\n" );
	}
	
	/**
	 *  Creates a textarea element. Called by <textarea></textarea>
	 *  @param array $tag an array-representation of the current tag element
	 *  @return void
	 */
	private function Textarea( $tag )
	{
		$this->incrementIndex();
		
		$this->appendHTML( $this->appendLabel( $tag ) );
		$this->appendHTML( "      <textarea id=\"" . $this->spaceToUnderscore( $tag['value'] ) . "\" cols=\"{$tag['attributes']['COLS']}\" rows=\"{$tag['attributes']['ROWS']}\" tabIndex=\"{$this->tabIndex}\" onChange=\"Nerdcules.SheetChanged = true;\"></textarea>" );
		$this->appendBreak( $tag );
		
		$this->appendForm( "      " . $this->spaceToUnderscore( $tag['value'] ) . ": '" . $this->spaceToUnderscore( $tag['value'] ) . "',\n" );
	}
	
	/**
	 *  Creates an HTML page based on the character sheet data passed in
	 *  @param string $xml the xml character sheet
	 */
	public function __construct( $xml )
	{
		$this->xml = $xml;
		$this->parse();
	}
	
	/**
	 *  Set a new character sheet; clears the old sheet, as well as the previously rendered sheet
	 *  @param string $xml the xml character sheet
	 *  @return void
	 */
	public function setXML( $xml )
	{
		$this->xml = $xml;
		$this->html = "";
		$this->scriptForm = "";
		$this->scriptElements = "";
	}
	
	/**
	 *  Retrieve the rendered sheet; this only returns the markup, use getScriptxxx for the scripts
	 *  @return string the rendered html page
	 */
	public function getHTML()
	{
		return $this->html;
	}
	
	/**
	 *  Retrieve the embedded stylesheet, if any.
	 *  @return string the stylehseet
	 */
	public function getStyle()
	{
		return $this->style;
	}
	
	/**
	 *  Retrieve the embedded custom script, if any.
	 *  @return string the javascript
	 */
	public function getScript()
	{
		return $this->script;
	}
	
	/**
	 *  Retrieve the rendered script to accompany the form elements
	 *  @return string the rendered form script
	 */
	public function getScriptForm()
	{
		return $this->scriptForm;
	}
	
	/**
	 *  Retrieve the rendered script to accompay the dot elements
	 *  @return string the rendered dot script
	 */
	public function getScriptElements()
	{
		return $this->scriptElements;
	}
	
	/**
	 *  Retrieve the descriptive information about this sheet
	 *  @return array[string]string as associative array containing all the descriptive information included in this sheet
	 */
	public function getInfo()
	{
		return array(
			"system" => $this->system,
			"game" => $this->game,
			"name" => $this->name,
			"copyright" => $this->copyright,
			"author" => $this->author,
			"description" => $this->description
		);
	}
	
	//this is the meat of this script
	/**
	 *  Parse the stored XML document into an HTML page
	 *  @return void
	 */
	public function parse()
	{
		//parse the data into an array
		$parser = xml_parser_create();
		$success = xml_parse_into_struct( $parser, $this->xml, $this->values );
		if ( $success === 0 )
		{
			if ( __PARSER_DEBUG__ )
			{
				echo xml_error_string( xml_get_error_code( $parser ) );
				exit();
			}
		}
		xml_parser_free( $parser );
		
		//start going through the array
		foreach ( $this->values as $tag )
		{
			//the XML data should be well-form and in lexical order
			//so just go through and format the tag
			
			switch ( $tag['tag'] )
			{
				case "SHEET":
					//root node
					if ( $tag['type'] == "open" )
					{
						//do anything that needs doing before getting started
					}
					if ( $tag['type'] == "close" )
					{
						//the end of the sheet, so print everything out
						//for $scriptForm and $scriptElements, get rid of the trailing comma
						////remove the last whitespace, remove the last comma, insert the last newline
						$this->scriptForm = rtrim( rtrim( $this->scriptForm ), ',' ) . "\n";
						$this->scriptElements = rtrim( rtrim( $this->scriptElements ), ',' ) . "\n";
					}
					break;
				case "HEAD":
					if ( $tag['type'] == "open" )
					{
						$this->openTag = "HEAD";
					}
					if ( $tag['type'] == "close" )
					{
						$this->openTag = "";
					}
					break;
				case "GAME":
					//<game> can only be inside <head>
					if ( $this->openTag == "HEAD" )
					{
						if ( $tag['type'] == "complete" )
						{
							$this->game = $tag['value'];
						}
					}
					break;
				case "SYSTEM":
					//<system> can only be inside <head>
					if ( $this->openTag == "HEAD" )
					{
						if ( $tag['type'] == "complete" )
						{
							$this->system = $tag['value'];
						}
					}
					break;
				case "NAME":
					//<name> can only be inside <head>
					if ( $this->openTag == "HEAD" )
					{
						if ( $tag['type'] == "complete" )
						{
							$this->name = $tag['value'];
						}
					}
					break;
				case "COPYRIGHT":
					//<copyright> can only be inside <head>
					if ( $this->openTag == "HEAD" )
					{
						if ( $tag['type'] == "complete" )
						{
							$this->copyright = $tag['value'];
						}
					}
					break;
				case "AUTHOR":
					//<author> can only be inside <head>
					if ( $this->openTag == "HEAD" )
					{
						if ( $tag['type'] == "complete" )
						{
							$this->author = $tag['value'];
						}
					}
					break;
				case "DESCRIPTION":
					//<description> can only be inside <head>; used to provide a description to search engines
					if ( $this->openTag == "HEAD" )
					{
						if ( $tag['type'] == "complete" )
						{
							$this->description = $tag['value'];
						}
					}
					break;
				case "STYLE":
					//<style> can only be inside <head>; used to define the style for this page
					if ( $this->openTag == "HEAD" )
					{
						if ( $tag['type'] == "complete" )
						{
							$this->style = $tag['value'];
						}
					}
					break;
				case "SCRIPT":
					//<script> can only be inside <head>; used to define the any custom scripts for this page
					if ( $this->openTag == "HEAD" )
					{
						if ( $tag['type'] == "complete" )
						{
							$this->script = $tag['value'];
						}
					}
					break;
				case "SET":
					//<set> signifies the beginning of a fieldset
					if ( $tag['type'] == "open" )
					{
						$this->openTag = "SET";
						$this->SetOpen( $tag );
					}
					if ( $tag['type'] == "close" )
					{
						$this->SetClose( $tag );
					}
					break;
				case "COLUMN":
					//<column> can only be inside of a <set>
					if ( $this->openTag == "SET" )
					{
						if ( $tag['type'] == "open" )
						{
							$this->openTag = "COLUMN";
							$this->parentTag = "SET";
							$this->ColumnOpen( $tag );
						}
						elseif ( $tag['type'] == "complete" )
						{
							//an empty column
							$this->ColumnOpen( $tag );
							$this->appendHTML( "&nbsp;\n" );
							$this->ColumnClose( $tag );
						}
					}
					if ( $this->parentTag == "SET" )
					{
						if ( $tag['type'] == "close" )
						{
							$this->openTag = "SET";
							$this->ColumnClose( $tag );
						}
					}
					break;
				case "ROW":
					//<row> can only be inside of a <column>
					if ( $this->openTag == "COLUMN" )
					{
						if ( $tag['type'] == "open" )
						{
							//start a row
							$this->inRow = true;
						}
						elseif ( $tag['type'] == "close" )
						{
							//close the row
							$this->inRow = false;
							$this->appendHTML( "<br />\n" );
						}
						else
						{
							//no defined behavior for a complete row tag ( <row /> )
						}
					}
					break;
				case "TEXT":
					//<text> can only be inside of a <column>; used for a text input
					if ( $this->openTag == "COLUMN" )
					{
						$this->Textfield( $tag );
					}
					break;
				case "SELECT":
					//<select> can only be inside of a <column>; used for a drop-down box
					if ( $this->openTag == "COLUMN" )
					{
						if ( $tag['type'] == "open" )
						{
							$this->currentTag = "SELECT";
							$this->SelectOpen( $tag );
						}
						if ( $tag['type'] == "close" )
						{
							$this->currentTag = "";
							$this->SelectClose( $tag );
						}
					}
					break;
				case "OPTION":
					//<option> can only be inside of a <select>; used for options in a drop-down box
					if ( $this->currentTag == "SELECT" )
					{
						$this->Option( $tag );
					}
					break;
				case "DOT":
					//<dot> can only be inside of a <column>; used for a Nerdcules.Dots element
					if ( $this->openTag == "COLUMN" )
					{
						$this->Dot( $tag );
					}
					break;
				case "SPINNER":
					//<dot> can only be inside of a <column>; used for a Nerdcules.Spinner element
					if ( $this->openTag == "COLUMN" )
					{
						$this->Spinner( $tag );
					}
					break;
				case "CHECK":
					//<check> can only be inside of a <column>; used for checkboxes
					if ( $this->openTag == "COLUMN" )
					{
						$this->Check( $tag );
					}
					break;
				case "TEXTAREA":
					//<textarea> can only be inside of a <column>; used for textareas
					if ( $this->openTag == "COLUMN" )
					{
						$this->Textarea( $tag );
					}
					break;
				case "BLANK":
					//inserts a blank line ( <br /> )
					$this->appendHTML( str_repeat( "  ", $tag['level'] ) . "<br />\n" );
					break;
				case "EM":
					//<head> can only be inside of a <column>; used for a heading ( <strong> )
					if ( $this->openTag == "COLUMN" )
					{
						//if break=false, don't print a <br />, otherwise print it
						$break = ( isset( $tag['attributes']['BREAK'] ) ) ? ( ( $tag['attributes']['BREAK'] == "false" ) ? "" : "<br />" ) : "<br />";
						$this->appendHTML( str_repeat( "  ", $tag['level'] ) . "<strong>{$tag['value']}</strong>{$break}\n" );
					}
					break;
				case "BREAK":
					//inserts a page break for printing
					$this->appendHTML( str_repeat( "  ", $tag['level'] ) . "<br class=\"break\" />\n" );
					break;
			}
		}
	}
}

?>