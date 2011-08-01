/**
 *  A collection of handy tools for my pet projects. So far, a dot slider for my character sheet manager.
 *
 *  Copyright (c) 2011 Joshua D. Hyrman
 *
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
 *
 *  If you find this code useful, or use it in your own project, I would love to know about it!
 *  If you find a bug or would like to submit a fix or improvement, please send it my way.
 *  
 *  @author Joshua D. Hyrman <jhyrman@gmail.com>
 *  @copyright Copyright 2011 Joshua D. Hyrman
 *  @version 0.0.1 (alpha)
 *  @requires Prototype
 *  @package Nerdcules
 */
 
//create the namespace if it doesn't exist
if ( typeof Nerdcules === "undefined" )
{
	/** @namespace */
	var Nerdcules = { };
}

/**
 *  Holds the session ID for server communication
 */
Nerdcules.session_id = "";

/**
 *  Holds the HTTP Request object
 */
Nerdcules.xmlhttp	= null;

/**
 *  Sets the session ID
 *  @param {String} session_id the session ID for the user
 */
Nerdcules.setSessionID = function( session_id )
{
	Nerdcules.session_id = session_id;
}

/**
 *  Parses a JSON string, and returns a JavaScript object.
 *  @author Douglas Crockford - http://www.JSON.org/json_parse.js
 *  @copyright 2011 Douglas Crockford
 *  @license Public Domain; NO WARRANTY EXPRESS OR IMPLIED. USE AT YOUR OWN RISK.
 *  @param {String} text the JSON string to parse
 *  @param {Function} reviver optional filter function, recieves each key and value, returns the filtered value
 *  @throws SyntaxError exception
 */
Nerdcules.json_parse = (function () {
    "use strict";

// This is a function that can parse a JSON text, producing a JavaScript
// data structure. It is a simple, recursive descent parser. It does not use
// eval or regular expressions, so it can be used as a model for implementing
// a JSON parser in other languages.

// We are defining the function inside of another function to avoid creating
// global variables.

    var at, // The index of the current character
        ch, // The current character
        escapee = {
            '"': '"',
            '\\': '\\',
            '/': '/',
            b: '\b',
            f: '\f',
            n: '\n',
            r: '\r',
            t: '\t'
        },
        text,

        error = function (m) {

// Call error when something is wrong.

            throw {
                name: 'SyntaxError',
                message: m,
                at: at,
                text: text
            };
        },

        next = function (c) {

// If a c parameter is provided, verify that it matches the current character.

            if (c && c !== ch) {
                error("Expected '" + c + "' instead of '" + ch + "'");
            }

// Get the next character. When there are no more characters,
// return the empty string.

            ch = text.charAt(at);
            at += 1;
            return ch;
        },

        number = function () {

// Parse a number value.

            var number,
                string = '';

            if (ch === '-') {
                string = '-';
                next('-');
            }
            while (ch >= '0' && ch <= '9') {
                string += ch;
                next();
            }
            if (ch === '.') {
                string += '.';
                while (next() && ch >= '0' && ch <= '9') {
                    string += ch;
                }
            }
            if (ch === 'e' || ch === 'E') {
                string += ch;
                next();
                if (ch === '-' || ch === '+') {
                    string += ch;
                    next();
                }
                while (ch >= '0' && ch <= '9') {
                    string += ch;
                    next();
                }
            }
            number = +string;
            if (!isFinite(number)) {
                error("Bad number");
            } else {
                return number;
            }
        },

        string = function () {

// Parse a string value.

            var hex,
                i,
                string = '',
                uffff;

// When parsing for string values, we must look for " and \ characters.

            if (ch === '"') {
                while (next()) {
                    if (ch === '"') {
                        next();
                        return string;
                    } else if (ch === '\\') {
                        next();
                        if (ch === 'u') {
                            uffff = 0;
                            for (i = 0; i < 4; i += 1) {
                                hex = parseInt(next(), 16);
                                if (!isFinite(hex)) {
                                    break;
                                }
                                uffff = uffff * 16 + hex;
                            }
                            string += String.fromCharCode(uffff);
                        } else if (typeof escapee[ch] === 'string') {
                            string += escapee[ch];
                        } else {
                            break;
                        }
                    } else {
                        string += ch;
                    }
                }
            }
            error("Bad string");
        },

        white = function () {

// Skip whitespace.

            while (ch && ch <= ' ') {
                next();
            }
        },

        word = function () {

// true, false, or null.

            switch (ch) {
            case 't':
                next('t');
                next('r');
                next('u');
                next('e');
                return true;
            case 'f':
                next('f');
                next('a');
                next('l');
                next('s');
                next('e');
                return false;
            case 'n':
                next('n');
                next('u');
                next('l');
                next('l');
                return null;
            }
            error("Unexpected '" + ch + "'");
        },

        value, // Place holder for the value function.

        array = function () {

// Parse an array value.

            var array = [];

            if (ch === '[') {
                next('[');
                white();
                if (ch === ']') {
                    next(']');
                    return array; // empty array
                }
                while (ch) {
                    array.push(value());
                    white();
                    if (ch === ']') {
                        next(']');
                        return array;
                    }
                    next(',');
                    white();
                }
            }
            error("Bad array");
        },

        object = function () {

// Parse an object value.

            var key,
                object = {};

            if (ch === '{') {
                next('{');
                white();
                if (ch === '}') {
                    next('}');
                    return object; // empty object
                }
                while (ch) {
                    key = string();
                    white();
                    next(':');
                    if (Object.hasOwnProperty.call(object, key)) {
                        error('Duplicate key "' + key + '"');
                    }
                    object[key] = value();
                    white();
                    if (ch === '}') {
                        next('}');
                        return object;
                    }
                    next(',');
                    white();
                }
            }
            error("Bad object");
        };

    value = function () {

// Parse a JSON value. It could be an object, an array, a string, a number,
// or a word.

        white();
        switch (ch) {
        case '{':
            return object();
        case '[':
            return array();
        case '"':
            return string();
        case '-':
            return number();
        default:
            return ch >= '0' && ch <= '9' ? number() : word();
        }
    };

// Return the json_parse function. It will have access to all of the above
// functions and variables.

    return function (source, reviver) {
        var result;

        text = source;
        at = 0;
        ch = ' ';
        result = value();
        white();
        if (ch) {
            error("Syntax error");
        }

// If there is a reviver function, we recursively walk the new structure,
// passing each name/value pair to the reviver function for possible
// transformation, starting with a temporary root object that holds the result
// in an empty key. If there is not a reviver function, we simply return the
// result.

        return typeof reviver === 'function' ? (function walk(holder, key) {
            var k, v, value = holder[key];
            if (value && typeof value === 'object') {
                for (k in value) {
                    if (Object.prototype.hasOwnProperty.call(value, k)) {
                        v = walk(value, k);
                        if (v !== undefined) {
                            value[k] = v;
                        } else {
                            delete value[k];
                        }
                    }
                }
            }
            return reviver.call(holder, key, value);
        }({'': result}, '')) : result;
    };
}());

/**
 *  Creates a new Dots object; used to represent dice pools and such.
 *  @class A series of dots to represent a value
 *  @param {String} Element The Element to draw the dots inside of (usually a span); can be anything accepted by Prototype $()
 *  @param {Number} Minimum The Minimum value
 *  @param {Number} maximum The maxmimum value (also sets the number of dots to draw)
 *  @param {Number} Initial Optional Initial value; defaults to equal minimum
 */
Nerdcules.Dots = function( element, minimum, maximum, initial, filled, empty )
{
	"use strict"; 
	
	/** @public */
	this.Version	= '0.0.1';
	
	this._name 		= element;
	this.element	= document.getElementById( element );
	if ( minimum < 0 )
	{
		throw( this._name + "-- Dots.Minimum cannot be less than 0" );
	}
	this.minimum	= minimum;
	if ( maximum < minimum )
	{
		throw( this._name + "-- Dots.Maximum cannot be less than Dots.Minimum" );
	}
	this.maximum	= maximum;
	this.initial	= ( typeof initial === 'undefined' ) ? this.minimum : initial;
	if ( this.initial < this.minimum )
	{
		this.initial = this.minimum;
	}
	
	this.filled		= ( typeof filled === 'undefined' ) ? "&#8226;" : filled;
	this.empty		= ( typeof empty === 'undefined' ) ? "&#9702;" : empty;
	
	this.current	= this.initial;
	
	/**
	 *  Clears the current selection (highlighted text) that sometimes happens when clicking too fast on up/down buttons
	 */
	this.clearSelection = function()
	{
		var selection;
		if( document.selection && document.selection.empty )
		{
			document.selection.empty() ;
		}
		else if( window.getSelection )
		{
			selection = window.getSelection();
			if( selection && selection.removeAllRanges )
			{
				selection.removeAllRanges();
			}
		}
	};
	
	/**
	 *  Paints the current value to the screen
	 */
	this.drawDots	= function()
	{
		this.clearSelection();
		var i = 0, html = "", numDots = 0;
		
		//add the filled dots
		for ( i = 0; i < this.current; i += 1 )
		{
			html = html + this.filled;
			numDots += 1;
			//draw a new line whenever the number of dots reaches a multiple of 10
			if ( ( numDots % 10 ) === 0 )
			{
				html = html + "<br />\n";
			}
		}
		
		//add the empty dots
		for ( i = 0; i < ( this.maximum - this.current ); i += 1 )
		{
			html = html + this.empty;
			numDots += 1;
			//draw a new line whenever the number of dots reaches a multiple of 10
			if ( ( numDots % 10 ) === 0 )
			{
				html = html + "<br />\n";
			}
		}
		
		this.element.innerHTML = html;
	};
	
	/**
	 *  Increments the current value and then paints it to the screen
	 */
	this.increment	= function()
	{
		if ( this.current < this.maximum )
		{
			this.current += 1;
		}
		
		//if the nerdcules sheet manager is also being used, let it know that the sheet has been changed
		if ( typeof SheetChanged !== false )
		{
			Nerdcules.SheetChanged = true;
		}
		
		this.drawDots();
	};
	
	/**
	 *  Decrements the current value and then paints it to the screen
	 */
	this.decrement	= function()
	{
		if ( this.current > this.minimum )
		{
			this.current -= 1;
		}
		
		//if the nerdcules sheet manager is also being used, let it know that the sheet has been changed
		if ( typeof SheetChanged !== false )
		{
			Nerdcules.SheetChanged = true;
		}
		
		this.drawDots();
	};
	
	/**
	 *  Returns the current value
	 *  @return Number the current value
	 */
	this.getValue	= function()
	{
		return this.current;
	};
	
	/**
	 *  Sets the current value
	 *  @param Number val the new value
	 */
	this.setValue	= function( val )
	{
		if ( ( val < this.minimum ) || ( val > this.maximum ) )
		{
			throw( this._name + "-- setValue: new value must be between minimum and maximum" );
		}
		else
		{
			this.current = val;
			this.drawDots();
		}
	};
	
	/*
	 * Capture keyPress events
	 * Increase when the right arrow key is pressed
	 * Decrease when the left arrow key is pressed
	 */
	this.keyPress = function( event )
	{
		//FF passes event, IE uses window.event
		var _event = ( event ) ? event : window.event;
		
		//right arrow
		if ( _event.keyCode === 39 )
		{
			this.increment();
			//FF uses stopPropagation method, IE uses cancelBubble property
			( _event.stopPropagation ) ? _event.stopPropagation() : _event.cancelBubble = true;
		}
		//left arrow
		if ( _event.keyCode === 37 )
		{
			this.decrement();
			//FF uses stopPropagation method, IE uses cancelBubble property
			( _event.stopPropagation ) ? _event.stopProagation() : _event.cancelBubble = true;
		}
	};
	
	this.drawDots();
};

/**
 *  Character Sheet holder
 */
Nerdcules.CharacterSheet = {};

/**
 *  State tracker for the sheet
 */
Nerdcules.SheetChanged = false;

/**
 *  Stringify the character sheet to a JSON object
 */
Nerdcules.sheetToJson = function()
{
	// the JSON string
	var j =
	"SavedSheet = \n" +
	"{\n" +
	"	Form:\n" +
	"	{\n";
	
	for ( var prop in Nerdcules.CharacterSheet.Form )
	{
		if ( Nerdcules.CharacterSheet.Form.hasOwnProperty( prop ) )
		{
			//  Name: "name",
			
			//see if this is a text element or a check element
			if ( document.getElementById( Nerdcules.CharacterSheet.Form[ prop ] ).checked )
			{
				j = j + "		" + prop + ": " + document.getElementById( Nerdcules.CharacterSheet.Form[ prop ] ).checked + ", \n";
			}
			else
			{
				j = j + "		" + prop + ": \"" + document.getElementById( Nerdcules.CharacterSheet.Form[ prop ] ).value.replace( new RegExp( "\"", "g" ), "\'" ) + "\", \n";
																																/*  ^ replace double-quotes with single-quotes */
			}
			
		}
	}
	
	j = j +
	"	}, \n" +
	"	Dots:\n" +
	"	{\n";
	
	for ( var prop in Nerdcules.CharacterSheet.Dots )
	{
		if ( Nerdcules.CharacterSheet.Dots.hasOwnProperty( prop ) )
		{
			j = j + "		" + prop + ": " + Nerdcules.CharacterSheet.Dots[ prop ].getValue() + ", \n";
		}
	}
	
	j = j +
	"	}\n" +
	"}";
	
	return j;
}

/**
 *  Save the character sheet to the server.
 *  Your saveSheet.php (or other server-side page) should accept the following
 *  parameters as POST data:
 *		session_id:		the user's session identifer; the server-side session data should have the user's ID for sheet ownership
 *		BlankID:		the character sheet prototype (blank sheet) that this character is based on
 *		CharName:		the name of this character
 *		SheetID:		if this is a filled sheet (existing character), this parameter will be set, meaning it should be overwritten
 *		json:			the JSON string for this character
 */
Nerdcules.saveSheet = function( BlankID, CharName, SheetID )
{
	//create the HTTP request instance
	var xmlhttp;
	//paramter string
	var parameters = "";
	
	//check for IE first
	if ( window.ActiveXObject )
	{
		var ActiveXModes = [ "Msxml2.XMLHTTP", "Microsoft.XMLHTTP" ];
		for ( var i = 0; i < ActiveXModes.length; i++ )
		{
			try
			{
				xmlhttp = new ActiveXObject( ActiveXModes[i] );
			}
			catch ( e )
			{
				//supress
			}
		}
	}
	//if the browser actually follows standards, used XMLHttpRequest
	else if ( window.XMLHttpRequest )
	{
		xmlhttp = new XMLHttpRequest();
	}
	else
	{
		throw "Cannot create any Request instance!";
	}
	
	xmlhttp.onreadystatechange = function()
	{
		if ( ( xmlhttp.readyState == 4 ) && ( xmlhttp.status == 200 ) )
		{
			alert( xmlhttp.responseText );
		}
	}
	
	parameters =
		"session_id=" + Nerdcules.session_id + "&" +
		"BlankID=" + encodeURIComponent( BlankID ) + "&" +
		"CharName=" + encodeURIComponent( CharName ) + "&";
	if ( typeof SheetID !== "undefined" )
	{
		parameters += "SheetID=" + encodeURIComponent( SheetID ) + "&";
	}
	parameters += "json=" + encodeURIComponent( Nerdcules.sheetToJson() );
	
	xmlhttp.open( "POST", "saveSheet.php", true );
	xmlhttp.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
	xmlhttp.send( parameters );
}

/**
 *  Delete the current character sheet from the server.
 *	Your deleteSheet.php (or other server-side page) should accept the following
 *  parameters as POST data:
 *		session_id:		the user's session identifier; the server-side session data should include the user's id for sheet ownership
 *		SheetID:		the character sheet ID to delete
 */
Nerdcules.deleteSheet = function( SheetID )
{
	var del = confirm( "Are you sure you want to delete?\nTHIS CANNOT BE UNDONE!" );
	if ( del !== true )
	{
		return;
	}
	
	//create the HTTP request instance
	var xmlhttp;
	//paramter string
	var parameters = "";
	
	//check for IE first
	if ( window.ActiveXObject )
	{
		var ActiveXModes = [ "Msxml2.XMLHTTP", "Microsoft.XMLHTTP" ];
		for ( var i = 0; i < ActiveXModes.length; i++ )
		{
			try
			{
				xmlhttp = new ActiveXObject( ActiveXModes[i] );
			}
			catch ( e )
			{
				//supress
			}
		}
	}
	//if the browser actually follows standards, used XMLHttpRequest
	else if ( window.XMLHttpRequest )
	{
		xmlhttp = new XMLHttpRequest();
	}
	else
	{
		throw "Cannot create any Request instance!";
	}
	
	xmlhttp.onreadystatechange = function()
	{
		if ( ( xmlhttp.readyState == 4 ) && ( xmlhttp.status == 200 ) )
		{
			alert( xmlhttp.responseText );
			self.close();
		}
	}
	
	parameters = "session_id=" + Nerdcules.session_id + "&SheetID=" + SheetID;
	
	xmlhttp.open( "POST", "deleteSheet.php", true );
	xmlhttp.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
	xmlhttp.send( parameters );
}

/**
 *  Parses the JSON string into an object, and applies the values to the form
 *  @param string sheet the stored JSON string
 */
Nerdcules.loadSheet = function( sheet )
{	
	//set all of the form elements
	for ( var prop in sheet.Form )
	{
		if ( document.getElementById( prop ).checked )
		{
			document.getElementById( prop ).checked = sheet.Form[ prop ];
		}
		else
		{
			document.getElementById( prop ).value = sheet.Form[ prop ];
		}
	}
	
	//set all of the Dots elements
	for ( var prop in sheet.Dots )
	{
		try
		{
			Nerdcules.CharacterSheet.Dots[ prop ].setValue( sheet.Dots[ prop ] );
		}
		catch ( e )
		{
			alert( e );
		}
	}
}

/**
 *  Periodically pings a stay-alive page on the server to keep the session active.
 *	Your stayAlive.php (or other server-side page) should accept the following
 *  parameters as GET data:
 *		session_id:		the user's session identifier
 *
 *  Your server page should then refresh the user's session timer. A simple PHP script is:
 *		session_start();
 *		echo "\n";
 *		exit();
 */
Nerdcules.stayAlive = function()
{
	//get the HTTP request instance
	var xmlhttp = null;
	
	//check for IE first
	if ( window.ActiveXObject )
	{
		var ActiveXModes = [ "Msxml2.XMLHTTP", "Microsoft.XMLHTTP" ];
		for ( var i = 0; i < ActiveXModes.length; i++ )
		{
			try
			{
				xmlhttp = new ActiveXObject( ActiveXModes[i] );
			}
			catch ( e )
			{
				//supress
			}
		}
	}
	//if the browser actually follows standards, used XMLHttpRequest
	else if ( window.XMLHttpRequest )
	{
		xmlhttp = new XMLHttpRequest();
	}
	else
	{
		throw "Cannot create any Request instance!";
	}
	
	xmlhttp.onreadystatechange = function()
	{
		if ( ( xmlhttp.readyState == 4 ) && ( xmlhttp.status == 200 ) )
		{
			window.setTimeout( "Nerdcules.stayAlive()", 60000, "JavaScript" );
								/* ping the server once a minute ( 1000 * 60 ) */
		}
	}
	
	xmlhttp.open( "GET", "stayAlive.php?session_id=" + Nerdcules.session_id, true );
	xmlhttp.send( null );
}