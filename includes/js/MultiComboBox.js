/*
	Copyright (c) 2004-2008, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/

/* SemanticScuttle: This script is a light modification of dojox.form.MultiComboBox
This fork allows specific use until DOJO 1.2.3 in Google CDN. */



if(!dojo._hasResource["js.MultiComboBox"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["js.MultiComboBox"] = true;
dojo.provide("js.MultiComboBox");
dojo.experimental("js.MultiComboBox"); 
dojo.require("dijit.form.ComboBox");
dojo.require("dijit.form.ValidationTextBox");

dojo.declare("js.MultiComboBox",
	[dijit.form.ValidationTextBox, dijit.form.ComboBoxMixin],{
	//
	// summary: A ComboBox that accpets multiple inputs on a single line?
	//
	// delimiter: String
	// 	The character to use to separate items in the ComboBox input
	delimiter: ",",
	_previousMatches: false,

	_setValueAttr: function(value){
		if (this.delimiter && value.length != 0){
			value = value+this.delimiter+" ";
			arguments[0] = this._addPreviousMatches(value);
		}
		this.inherited(arguments);
	},

	_addPreviousMatches: function(/* String */text){
		if(this._previousMatches){
			if(!text.match(new RegExp("^"+this._previousMatches))){
				text = this._previousMatches+text;
			}			
		}
		text = this._cleanupDelimiters(text);  // SScuttle: this line was moved
		return text; // String
	},

	_cleanupDelimiters: function(/* String */text){
		if(this.delimiter){
			text = text.replace(new RegExp("  +"), " ");
			text = text.replace(new RegExp("^ *"+this.delimiter+"* *"), "");
			text = text.replace(new RegExp(this.delimiter+" *"+this.delimiter), this.delimiter);
		}
		return text;
	},
			
	_autoCompleteText: function(/* String */text){
		arguments[0] = this._addPreviousMatches(text);
		this.inherited(arguments);
	},

	_startSearch: function(/* String */text){
		text = this._cleanupDelimiters(text);
		var re = new RegExp("^.*"+this.delimiter+" *");
		
		if((this._previousMatches = text.match(re))){
			arguments[0] = text.replace(re, "");
		}
		this.inherited(arguments);
	}		
});

}