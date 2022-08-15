/*
 * Mentions Input
 * Version 1.0.2
 * Written by: Kenneth Auchenberg (Podio)
 *
 * Using underscore.js
 *
 * License: MIT License - http://www.opensource.org/licenses/mit-license.php
 */
var nbr = 0;
var isoneditpage = false;
var mentionsCollectionValEdit = [];
(function ($, _, undefined) {

    // Settings
    var KEY = { BACKSPACE : 8, TAB : 9, RETURN : 13, ESC : 27, LEFT : 37, UP : 38, RIGHT : 39, DOWN : 40, COMMA : 188, SPACE : 32, HOME : 36, END : 35 }; // Keys "enum"

    //Default settings
    var defaultSettings = {
        triggerChar   : '@', //Char that respond to event
        onDataRequest : $.noop, //Function where we can search the data
        minChars      : 1, //Minimum chars to fire the event
        allowRepeat   : true, //Allow repeat mentions
        showAvatars   : true, //Show the avatars
        elastic       : false, //Grow the textarea automatically
        defaultValue  : '',
        onCaret       : false,
        classes       : {
            autoCompleteItemActive : "active" //Classes to apply in each item
        },
        templates     : {
            wrapper                    : _.template('<div class="mentions-input-box"></div>'),
            autocompleteList           : _.template('<div class="mentions-autocomplete-list"></div>'),
            autocompleteListItem       : _.template('<li data-ref-id="<%= id %>" data-ref-type="<%= type %>" data-display="<%= display %>"><%= content %></li>'),
            autocompleteListItemAvatar : _.template('<%= avatar %>'),
            autocompleteListItemIcon   : _.template('<div class="icon <%= icon %>"></div>'),
            mentionsOverlay            : _.template('<div class="highlighter"><div></div></div>'),
            mentionItemSyntax          : _.template('@_user_<%= id %>'),
            mentionItemHighlight       : _.template('<strong><span><%= value %></span></strong>')
        }
    };

    //Class util
    var utils = {
	    //Encodes the character with _.escape function (undersocre)
        htmlEncode       : function (str) {
            return _.escape(str);
        },
        //Encodes the character to be used with RegExp
        regexpEncode     : function (str) {
            return str;//str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
        },
	    highlightTerm    : function (value, term) {
            if (!term && !term.length) {
                return value;
            }
            return value.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + term + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<b>$1</b>");
        },
        //Sets the caret in a valid position
        setCaratPosition : function (domNode, caretPos) {
            if (domNode.createTextRange) {
                var range = domNode.createTextRange();
                range.move('character', caretPos);
                range.select();
            } else {
                if (domNode.selectionStart) {
                    domNode.focus();
                    domNode.setSelectionRange(caretPos, caretPos);
                } else {
                    domNode.focus();
                }
            }
        },
	    //Deletes the white spaces
        rtrim: function(string) {
            return string.replace(/\s+$/,"");
        }
    };

    //Main class of MentionsInput plugin
    var MentionsInput = function (settings) {

        var domInput,
            elmInputBox,
            elmInputWrapper,
            elmAutocompleteList,
            elmWrapperBox,
            elmMentionsOverlay,
            elmActiveAutoCompleteItem,
            autocompleteItemCollection = {},
            inputBuffer = [],
            currentDataQuery = '';

	    //Mix the default setting with the users settings
        settings = $.extend(true, {}, defaultSettings, settings );

	    //Initializes the text area target
        function initTextarea() {
            elmInputBox = $(domInput); //Get the text area target

            //If the text area is already configured, return
            if (elmInputBox.attr('data-mentions-input') === 'true') {
                return;
            }

            elmInputWrapper = elmInputBox.parent(); //Get the DOM element parent
            elmWrapperBox = $(settings.templates.wrapper());
            elmInputBox.wrapAll(elmWrapperBox); //Wrap all the text area into the div elmWrapperBox
            elmWrapperBox = elmInputWrapper.find('> div.mentions-input-box'); //Obtains the div elmWrapperBox that now contains the text area

            elmInputBox.attr('data-mentions-input', 'true'); //Sets the attribute data-mentions-input to true -> Defines if the text area is already configured
            elmInputBox.bind('keydown', onInputBoxKeyDown); //Bind the keydown event to the text area
            elmInputBox.bind('keypress', onInputBoxKeyPress); //Bind the keypress event to the text area
            elmInputBox.bind('click', onInputBoxClick); //Bind the click event to the text area
            elmInputBox.bind('blur', onInputBoxBlur); //Bind the blur event to the text area

            if (navigator.userAgent.indexOf("MSIE 8") > -1) {
                elmInputBox.bind('propertychange', onInputBoxInput); //IE8 won't fire the input event, so let's bind to the propertychange
            } else {
                elmInputBox.bind('input', onInputBoxInput); //Bind the input event to the text area
            }

            // Elastic textareas, grow automatically
            if( settings.elastic ) {
                elmInputBox.elastic();
            }
        }

        //Initializes the autocomplete list, append to elmWrapperBox and delegate the mousedown event to li elements
        function initAutocomplete() {
            elmAutocompleteList = $(settings.templates.autocompleteList()); //Get the HTML code for the list
            elmAutocompleteList.appendTo(elmWrapperBox); //Append to elmWrapperBox element
            elmAutocompleteList.delegate('li', 'mousedown', onAutoCompleteItemClick); //Delegate the event
        }

        //Initializes the mentions' overlay
        function initMentionsOverlay() {
            elmMentionsOverlay = $('.jqueryHashtags'); //Get the HTML code of the mentions' overlay
            //elmMentionsOverlay.prependTo(elmWrapperBox); //Insert into elmWrapperBox the mentions overlay
        }

	    //Updates the values of the main variables
        function updateValues() {

          if(sesJqueryObject('#sessmoothbox_main').length){
              var className = 'highlighter_edit';
              var textAreal ='edit_activity_body';
              var elmMentionsOverlayElem = 'jqueryHashtags_edit';
             }else{
              var textAreal ='activity_body';
              var className = 'highlighter';
               var elmMentionsOverlayElem = 'jqueryHashtags';
             }
            var classNameElem = $(domInput).closest('.jqueryHashtags').find('div').eq(0);
           // var textAreal = $(domInput);
            elmMentionsOverlay = $('.'+elmMentionsOverlayElem);
            var syntaxMessage = getInputBoxValue(); //Get the actual value of the text area

            EditFieldValue = syntaxMessage;
           
            var id = $(domInput).attr('id'); 
            if(id){
              if(typeof mentiondataarray["mention_data_"+id]  != 'undefined'){
                mentionsCollection = mentiondataarray["mention_data_"+id];
              }
            }
            _.each(mentionsCollection, function (mention) {
                var textSyntax = settings.templates.mentionItemSyntax(mention);
                syntaxMessage = syntaxMessage.replace(new RegExp(utils.regexpEncode(mention.value), 'g'), textSyntax);
            });

            //var mentionText = utils.htmlEncode(syntaxMessage); //Encode the syntaxMessage
            var mentionText = syntaxMessage;
            _.each(mentionsCollection, function (mention) {
                var formattedMention = _.extend({}, mention, {value: utils.htmlEncode(mention.value)});
                var textSyntax = settings.templates.mentionItemSyntax(formattedMention);
                var textHighlight = settings.templates.mentionItemHighlight(formattedMention);

                mentionText = mentionText.replace(new RegExp(utils.regexpEncode(textSyntax), 'g'), textHighlight);
            });

            mentionText = mentionText.replace(/\n/g, '<br />'); //Replace the escape character for <br />
            mentionText = mentionText.replace(/ {2}/g, '&nbsp; '); //Replace the 2 preceding token to &nbsp;

            elmInputBox.data('messageText', syntaxMessage); //Save the messageText to elmInputBox
	          elmInputBox.trigger('updated');
            
            
            var str = mentionText;
             
            classNameElem.css("width",$(domInput).css("width"));
            str = str.replace(/\n/g, '<br>');
            
            var tagslistarr = str.match(/\B(#[^\s[!\"\#$%&'()*+,\-.\/\\:;<=>?@\[\]\^`{|}~]+)/g);
            if(tagslistarr && tagslistarr.length){
              for(var i=0;i<tagslistarr.length;i++){
                //hashtag must be on 1st position
                if(tagslistarr[i].indexOf('#') != 0 && str.indexOf(tagslistarr[i]) < 0 )
                  continue;
                var regex = new RegExp(tagslistarr[i], "g");
               str = str.replace(regex, '<strong></span>'+tagslistarr[i]+'</span></strong>');
              } 
            }
						
						
            /*if(!str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)?#([a-zA-Z0-9]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)?@([a-zA-Z0-9]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)?#([\u0600-\u06FF]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)?@([\u0600-\u06FF]+)/g)) {
              if(!str.match(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))#/g)) { //arabic support
                str = str.replace(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<strong><span>#$1</span></strong>');
              }else{
                str = str.replace(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<strong></span>#$1</span></strong>');
              }
            }*/
            
            
            
            
var p = str;
var rx = /([\uD800-\uDBFF][\uDC00-\uDFFF](?:[\u200D\uFE0F][\uD800-\uDBFF][\uDC00-\uDFFF]){2,}|\uD83D\uDC69(?:\u200D(?:(?:\uD83D\uDC69\u200D)?\uD83D\uDC67|(?:\uD83D\uDC69\u200D)?\uD83D\uDC66)|\uD83C[\uDFFB-\uDFFF])|\uD83D\uDC69\u200D(?:\uD83D\uDC69\u200D)?\uD83D\uDC66\u200D\uD83D\uDC66|\uD83D\uDC69\u200D(?:\uD83D\uDC69\u200D)?\uD83D\uDC67\u200D(?:\uD83D[\uDC66\uDC67])|\uD83C\uDFF3\uFE0F\u200D\uD83C\uDF08|(?:\uD83C[\uDFC3\uDFC4\uDFCA]|\uD83D[\uDC6E\uDC71\uDC73\uDC77\uDC81\uDC82\uDC86\uDC87\uDE45-\uDE47\uDE4B\uDE4D\uDE4E\uDEA3\uDEB4-\uDEB6]|\uD83E[\uDD26\uDD37-\uDD39\uDD3D\uDD3E\uDDD6-\uDDDD])(?:\uD83C[\uDFFB-\uDFFF])\u200D[\u2640\u2642]\uFE0F|\uD83D\uDC69(?:\uD83C[\uDFFB-\uDFFF])\u200D(?:\uD83C[\uDF3E\uDF73\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92])|(?:\uD83C[\uDFC3\uDFC4\uDFCA]|\uD83D[\uDC6E\uDC6F\uDC71\uDC73\uDC77\uDC81\uDC82\uDC86\uDC87\uDE45-\uDE47\uDE4B\uDE4D\uDE4E\uDEA3\uDEB4-\uDEB6]|\uD83E[\uDD26\uDD37-\uDD39\uDD3C-\uDD3E\uDDD6-\uDDDF])\u200D[\u2640\u2642]\uFE0F|\uD83C\uDDFD\uD83C\uDDF0|\uD83C\uDDF6\uD83C\uDDE6|\uD83C\uDDF4\uD83C\uDDF2|\uD83C\uDDE9(?:\uD83C[\uDDEA\uDDEC\uDDEF\uDDF0\uDDF2\uDDF4\uDDFF])|\uD83C\uDDF7(?:\uD83C[\uDDEA\uDDF4\uDDF8\uDDFA\uDDFC])|\uD83C\uDDE8(?:\uD83C[\uDDE6\uDDE8\uDDE9\uDDEB-\uDDEE\uDDF0-\uDDF5\uDDF7\uDDFA-\uDDFF])|(?:\u26F9|\uD83C[\uDFCB\uDFCC]|\uD83D\uDD75)(?:\uFE0F\u200D[\u2640\u2642]|(?:\uD83C[\uDFFB-\uDFFF])\u200D[\u2640\u2642])\uFE0F|(?:\uD83D\uDC41\uFE0F\u200D\uD83D\uDDE8|\uD83D\uDC69(?:\uD83C[\uDFFB-\uDFFF])\u200D[\u2695\u2696\u2708]|\uD83D\uDC69\u200D[\u2695\u2696\u2708]|\uD83D\uDC68(?:(?:\uD83C[\uDFFB-\uDFFF])\u200D[\u2695\u2696\u2708]|\u200D[\u2695\u2696\u2708]))\uFE0F|\uD83C\uDDF2(?:\uD83C[\uDDE6\uDDE8-\uDDED\uDDF0-\uDDFF])|\uD83D\uDC69\u200D(?:\uD83C[\uDF3E\uDF73\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\u2764\uFE0F\u200D(?:\uD83D\uDC8B\u200D(?:\uD83D[\uDC68\uDC69])|\uD83D[\uDC68\uDC69]))|\uD83C\uDDF1(?:\uD83C[\uDDE6-\uDDE8\uDDEE\uDDF0\uDDF7-\uDDFB\uDDFE])|\uD83C\uDDEF(?:\uD83C[\uDDEA\uDDF2\uDDF4\uDDF5])|\uD83C\uDDED(?:\uD83C[\uDDF0\uDDF2\uDDF3\uDDF7\uDDF9\uDDFA])|\uD83C\uDDEB(?:\uD83C[\uDDEE-\uDDF0\uDDF2\uDDF4\uDDF7])|[#\*0-9]\uFE0F\u20E3|\uD83C\uDDE7(?:\uD83C[\uDDE6\uDDE7\uDDE9-\uDDEF\uDDF1-\uDDF4\uDDF6-\uDDF9\uDDFB\uDDFC\uDDFE\uDDFF])|\uD83C\uDDE6(?:\uD83C[\uDDE8-\uDDEC\uDDEE\uDDF1\uDDF2\uDDF4\uDDF6-\uDDFA\uDDFC\uDDFD\uDDFF])|\uD83C\uDDFF(?:\uD83C[\uDDE6\uDDF2\uDDFC])|\uD83C\uDDF5(?:\uD83C[\uDDE6\uDDEA-\uDDED\uDDF0-\uDDF3\uDDF7-\uDDF9\uDDFC\uDDFE])|\uD83C\uDDFB(?:\uD83C[\uDDE6\uDDE8\uDDEA\uDDEC\uDDEE\uDDF3\uDDFA])|\uD83C\uDDF3(?:\uD83C[\uDDE6\uDDE8\uDDEA-\uDDEC\uDDEE\uDDF1\uDDF4\uDDF5\uDDF7\uDDFA\uDDFF])|\uD83C\uDFF4\uDB40\uDC67\uDB40\uDC62(?:\uDB40\uDC77\uDB40\uDC6C\uDB40\uDC73|\uDB40\uDC73\uDB40\uDC63\uDB40\uDC74|\uDB40\uDC65\uDB40\uDC6E\uDB40\uDC67)\uDB40\uDC7F|\uD83D\uDC68(?:\u200D(?:\u2764\uFE0F\u200D(?:\uD83D\uDC8B\u200D)?\uD83D\uDC68|(?:(?:\uD83D[\uDC68\uDC69])\u200D)?\uD83D\uDC66\u200D\uD83D\uDC66|(?:(?:\uD83D[\uDC68\uDC69])\u200D)?\uD83D\uDC67\u200D(?:\uD83D[\uDC66\uDC67])|\uD83C[\uDF3E\uDF73\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92])|(?:\uD83C[\uDFFB-\uDFFF])\u200D(?:\uD83C[\uDF3E\uDF73\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]))|\uD83C\uDDF8(?:\uD83C[\uDDE6-\uDDEA\uDDEC-\uDDF4\uDDF7-\uDDF9\uDDFB\uDDFD-\uDDFF])|\uD83C\uDDF0(?:\uD83C[\uDDEA\uDDEC-\uDDEE\uDDF2\uDDF3\uDDF5\uDDF7\uDDFC\uDDFE\uDDFF])|\uD83C\uDDFE(?:\uD83C[\uDDEA\uDDF9])|\uD83C\uDDEE(?:\uD83C[\uDDE8-\uDDEA\uDDF1-\uDDF4\uDDF6-\uDDF9])|\uD83C\uDDF9(?:\uD83C[\uDDE6\uDDE8\uDDE9\uDDEB-\uDDED\uDDEF-\uDDF4\uDDF7\uDDF9\uDDFB\uDDFC\uDDFF])|\uD83C\uDDEC(?:\uD83C[\uDDE6\uDDE7\uDDE9-\uDDEE\uDDF1-\uDDF3\uDDF5-\uDDFA\uDDFC\uDDFE])|\uD83C\uDDFA(?:\uD83C[\uDDE6\uDDEC\uDDF2\uDDF3\uDDF8\uDDFE\uDDFF])|\uD83C\uDDEA(?:\uD83C[\uDDE6\uDDE8\uDDEA\uDDEC\uDDED\uDDF7-\uDDFA])|\uD83C\uDDFC(?:\uD83C[\uDDEB\uDDF8])|(?:\u26F9|\uD83C[\uDFCB\uDFCC]|\uD83D\uDD75)(?:\uD83C[\uDFFB-\uDFFF])|(?:\uD83C[\uDFC3\uDFC4\uDFCA]|\uD83D[\uDC6E\uDC71\uDC73\uDC77\uDC81\uDC82\uDC86\uDC87\uDE45-\uDE47\uDE4B\uDE4D\uDE4E\uDEA3\uDEB4-\uDEB6]|\uD83E[\uDD26\uDD37-\uDD39\uDD3D\uDD3E\uDDD6-\uDDDD])(?:\uD83C[\uDFFB-\uDFFF])|(?:[\u261D\u270A-\u270D]|\uD83C[\uDF85\uDFC2\uDFC7]|\uD83D[\uDC42\uDC43\uDC46-\uDC50\uDC66\uDC67\uDC70\uDC72\uDC74-\uDC76\uDC78\uDC7C\uDC83\uDC85\uDCAA\uDD74\uDD7A\uDD90\uDD95\uDD96\uDE4C\uDE4F\uDEC0\uDECC]|\uD83E[\uDD18-\uDD1C\uDD1E\uDD1F\uDD30-\uDD36\uDDD1-\uDDD5])(?:\uD83C[\uDFFB-\uDFFF])|\uD83D\uDC68(?:\u200D(?:(?:(?:\uD83D[\uDC68\uDC69])\u200D)?\uD83D\uDC67|(?:(?:\uD83D[\uDC68\uDC69])\u200D)?\uD83D\uDC66)|\uD83C[\uDFFB-\uDFFF])|(?:[\u261D\u26F9\u270A-\u270D]|\uD83C[\uDF85\uDFC2-\uDFC4\uDFC7\uDFCA-\uDFCC]|\uD83D[\uDC42\uDC43\uDC46-\uDC50\uDC66-\uDC69\uDC6E\uDC70-\uDC78\uDC7C\uDC81-\uDC83\uDC85-\uDC87\uDCAA\uDD74\uDD75\uDD7A\uDD90\uDD95\uDD96\uDE45-\uDE47\uDE4B-\uDE4F\uDEA3\uDEB4-\uDEB6\uDEC0\uDECC]|\uD83E[\uDD18-\uDD1C\uDD1E\uDD1F\uDD26\uDD30-\uDD39\uDD3D\uDD3E\uDDD1-\uDDDD])(?:\uD83C[\uDFFB-\uDFFF])?|(?:[\u231A\u231B\u23E9-\u23EC\u23F0\u23F3\u25FD\u25FE\u2614\u2615\u2648-\u2653\u267F\u2693\u26A1\u26AA\u26AB\u26BD\u26BE\u26C4\u26C5\u26CE\u26D4\u26EA\u26F2\u26F3\u26F5\u26FA\u26FD\u2705\u270A\u270B\u2728\u274C\u274E\u2753-\u2755\u2757\u2795-\u2797\u27B0\u27BF\u2B1B\u2B1C\u2B50\u2B55]|\uD83C[\uDC04\uDCCF\uDD8E\uDD91-\uDD9A\uDDE6-\uDDFF\uDE01\uDE1A\uDE2F\uDE32-\uDE36\uDE38-\uDE3A\uDE50\uDE51\uDF00-\uDF20\uDF2D-\uDF35\uDF37-\uDF7C\uDF7E-\uDF93\uDFA0-\uDFCA\uDFCF-\uDFD3\uDFE0-\uDFF0\uDFF4\uDFF8-\uDFFF]|\uD83D[\uDC00-\uDC3E\uDC40\uDC42-\uDCFC\uDCFF-\uDD3D\uDD4B-\uDD4E\uDD50-\uDD67\uDD7A\uDD95\uDD96\uDDA4\uDDFB-\uDE4F\uDE80-\uDEC5\uDECC\uDED0-\uDED2\uDEEB\uDEEC\uDEF4-\uDEF8]|\uD83E[\uDD10-\uDD3A\uDD3C-\uDD3E\uDD40-\uDD45\uDD47-\uDD4C\uDD50-\uDD6B\uDD80-\uDD97\uDDC0\uDDD0-\uDDE6])|(?:[#\*0-9\xA9\xAE\u203C\u2049\u2122\u2139\u2194-\u2199\u21A9\u21AA\u231A\u231B\u2328\u23CF\u23E9-\u23F3\u23F8-\u23FA\u24C2\u25AA\u25AB\u25B6\u25C0\u25FB-\u25FE\u2600-\u2604\u260E\u2611\u2614\u2615\u2618\u261D\u2620\u2622\u2623\u2626\u262A\u262E\u262F\u2638-\u263A\u2640\u2642\u2648-\u2653\u2660\u2663\u2665\u2666\u2668\u267B\u267F\u2692-\u2697\u2699\u269B\u269C\u26A0\u26A1\u26AA\u26AB\u26B0\u26B1\u26BD\u26BE\u26C4\u26C5\u26C8\u26CE\u26CF\u26D1\u26D3\u26D4\u26E9\u26EA\u26F0-\u26F5\u26F7-\u26FA\u26FD\u2702\u2705\u2708-\u270D\u270F\u2712\u2714\u2716\u271D\u2721\u2728\u2733\u2734\u2744\u2747\u274C\u274E\u2753-\u2755\u2757\u2763\u2764\u2795-\u2797\u27A1\u27B0\u27BF\u2934\u2935\u2B05-\u2B07\u2B1B\u2B1C\u2B50\u2B55\u3030\u303D\u3297\u3299]|\uD83C[\uDC04\uDCCF\uDD70\uDD71\uDD7E\uDD7F\uDD8E\uDD91-\uDD9A\uDDE6-\uDDFF\uDE01\uDE02\uDE1A\uDE2F\uDE32-\uDE3A\uDE50\uDE51\uDF00-\uDF21\uDF24-\uDF93\uDF96\uDF97\uDF99-\uDF9B\uDF9E-\uDFF0\uDFF3-\uDFF5\uDFF7-\uDFFF]|\uD83D[\uDC00-\uDCFD\uDCFF-\uDD3D\uDD49-\uDD4E\uDD50-\uDD67\uDD6F\uDD70\uDD73-\uDD7A\uDD87\uDD8A-\uDD8D\uDD90\uDD95\uDD96\uDDA4\uDDA5\uDDA8\uDDB1\uDDB2\uDDBC\uDDC2-\uDDC4\uDDD1-\uDDD3\uDDDC-\uDDDE\uDDE1\uDDE3\uDDE8\uDDEF\uDDF3\uDDFA-\uDE4F\uDE80-\uDEC5\uDECB-\uDED2\uDEE0-\uDEE5\uDEE9\uDEEB\uDEEC\uDEF0\uDEF3-\uDEF8]|\uD83E[\uDD10-\uDD3A\uDD3C-\uDD3E\uDD40-\uDD45\uDD47-\uDD4C\uDD50-\uDD6B\uDD80-\uDD97\uDDC0\uDDD0-\uDDE6])\uFE0F)/;







            //FeelingEmojis Work
            if(sesJqueryObject('#sesadvancedactivity_feeling_emojisa').length > 0) {
              str = emojiscontent.toImage(str);
            }
            //var res = str.replace(/&lt;br ?\/\>|&lt;br ?\/&rt;|\<br ?\/\>/g, "").split(' '); //p.split(customres).filter(Boolean); 
            var checkEmoji = false;
//             for(i=0;i < res.length;i++) {
//               var emojisString = res[i];
//               var isLiExist = sesJqueryObject('.sesfeelact_simemoji').find("li[data-icon='"+emojisString+"']").length;
//               if(isLiExist > 0 ){
//                 checkEmoji = true;
//                 var imgSrc = sesJqueryObject('.sesfeelact_simemoji').find("li[data-icon='"+emojisString+"']").find('a').find('img').attr('src');
//                 if(isonCommentBox) {
//                   str = str.replace(emojisString, '<img class="sesfeelact_status_icon_small" src="'+imgSrc+'">');
//                 } else {
//                   if(!isonCommentBox){
//                     if(sesadvancedactivitybigtext && !composeInstancecheck && !isoneditpage) {
//                       var textlength = $(domInput).val().length;
//                       if(textlength <= sesAdvancedactivitytextlimit) {
//                         var height = sesAdvancedactivityfonttextsize;
//                         var width = sesAdvancedactivityfonttextsize;
//                         str = str.replace(emojisString, '<img class="sesfeelact_status_icon_big" src="'+imgSrc+'">');
//                       } else {
//                         str = str.replace(emojisString, '<img class="sesfeelact_status_icon_small" src="'+imgSrc+'">');
//                       }
//                     }
//                   }
//                 }
//               }
//             }
            //FeelingEmojis Work
            
            //Text size increase in status box
            var composeInstancecheck = false;
          if(typeof composeInstance != 'undefined'){
            composeInstance.plugins.each(function(plugin) { 
              if(plugin.active) {
                composeInstancecheck = true;
              }
            });
            
          } 
          else{
             isoneditpage = false;
             composeInstancecheck = true;
          }

          if(!isonCommentBox){
            if(sesadvancedactivitybigtext && !composeInstancecheck && !isoneditpage) {
              var textlength = $(domInput).val().length;
              if(textlength <= sesAdvancedactivitytextlimit) {
                classNameElem.css("fontSize", sesAdvancedactivityfonttextsize);
                $(domInput).css("fontSize", sesAdvancedactivityfonttextsize);
                
                //Feed Background image work
                if($('feedbgid')) {
                  
                  var feelingactivity_type = 1;
                  if(document.getElementById('feelingactivity_type')) {
                    var feelingactivity_type = document.getElementById('feelingactivity_type').value;
                  }
                  if(feelingactivity_type != 2) {
                    var feedbgid = sesJqueryObject('#feedbgid').val();
                    var activitylng = sesJqueryObject('#activitylng').val();
                    if(feedbgid) {
                        var feedagainsrcurl = sesJqueryObject('#feed_bg_image_' + feedbgid).attr('src');
                        sesJqueryObject('.sesact_post_box').css("background-image", "url(" + feedagainsrcurl + ")");
                        sesJqueryObject('#feedbgid_isphoto').val(1);
                    }
                    //sesJqueryObject('#feedbg_main_continer').css('display','block');
                    if(feedbgid) {
                      sesJqueryObject('#activity-form').addClass('feed_background_image');
                    }
                    if(feedbgid == 0) {
                      sesJqueryObject('#activity-form').removeClass('feed_background_image');
                    }
                    if(activitylng) {
                      sesJqueryObject('#feedbgid_isphoto').val(0);
                      sesJqueryObject('.sesact_post_box').css('background-image', 'none');
                      sesJqueryObject('#activity-form').removeClass('feed_background_image');
                      sesJqueryObject('#feedbg_main_continer').css('display','none');
                    }
                  }
                }
                //Feed Background image work
              } else {
                classNameElem.css("fontSize", '');
                $(domInput).css("fontSize", '');
                //Feed Background image work
                //if($('feedbgid')) {
                  sesJqueryObject('#feedbgid_isphoto').val(0);
                  sesJqueryObject('.sesact_post_box').css('background-image', 'none');
                  sesJqueryObject('#activity-form').removeClass('feed_background_image');
                  //sesJqueryObject('#feedbg_main_continer').css('display','none');
                //}
                //Feed Background image work
              }
            }
          }
          
//           if(checkEmoji) {
//             classNameElem.css("fontSize", '');
//             $(domInput).css("fontSize", '');
//           }
        
          if((composeInstancecheck && !isoneditpage) || isonCommentBox) {
            classNameElem.css("fontSize", '');
            $(domInput).css("fontSize", '');
          }
          //Text size increase in status box
          $(domInput).closest('.mentions-input-box').find('div').eq(0).find('div').eq(0).html(str); //Insert into a div of the elmMentionsOverlay the mention text
					
					if(typeof feedUpdateFunction != 'undefined'){
            feedUpdateFunction();
          }
        }
                
        //Cleans the buffer
        function resetBuffer() {
            inputBuffer = [];
        }

	    //Updates the mentions collection
        function updateMentionsCollection() {
            var inputText = getInputBoxValue(); //Get the actual value of text area

	        //Returns the values that doesn't match the condition
            mentionsCollection = _.reject(mentionsCollection, function (mention, index) {
                return !mention.value || inputText.indexOf(mention.value) == -1;
            });
            mentionsCollection = _.compact(mentionsCollection); //Delete all the falsy values of the array and return the new array
        }

	    //Adds mention to mentions collections
        function addMention(mention) {

            var currentMessage = getInputBoxValue(),
                caretStart = elmInputBox[0].selectionStart,
                shortestDistance = false,
                bestLastIndex = false;

            // Using a regex to figure out positions
            var regex = new RegExp("\\" + settings.triggerChar + currentDataQuery, "gi"),
                regexMatch;
            
            while(regexMatch = regex.exec(currentMessage)) {
                if (shortestDistance === false || Math.abs(regex.lastIndex - caretStart) < shortestDistance) {
                    shortestDistance = Math.abs(regex.lastIndex - caretStart);
                    bestLastIndex = regex.lastIndex;
                }
            }

            var startCaretPosition = bestLastIndex - currentDataQuery.length - 1; //Set the start caret position (right before the @)
            var currentCaretPosition = bestLastIndex; //Set the current caret position (right after the end of the "mention")


            var start = currentMessage.substr(0, startCaretPosition);
            var end = currentMessage.substr(currentCaretPosition, currentMessage.length);
            var startEndIndex = (start + mention.value).length + 1;

            // See if there's the same mention in the list
            if( !_.find(mentionsCollection, function (object) { return object.id == mention.id; }) ) {
                mentionsCollection.push(mention);//Add the mention to mentionsColletions
                var id = $(domInput).attr('id');
                if(id){
                  if(typeof mentiondataarray["mention_data_"+id]  == 'undefined'){
                    mentiondataarray["mention_data_"+id] = mentionsCollection;
                  }else{
                    mentiondataarray["mention_data_"+id].push(mention);  
                  }
                }
            }
            
            // Cleaning before inserting the value, otherwise auto-complete would be triggered with "old" inputbuffer
            resetBuffer();
            currentDataQuery = '';
            hideAutoComplete();

            // Mentions and syntax message
            var updatedMessageText = start + mention.value + ' ' + end;
            elmInputBox.val(updatedMessageText); //Set the value to the txt area
	          elmInputBox.trigger('mention');
            updateValues('addmention');

            // Set correct focus and selection
            elmInputBox.focus();
            utils.setCaratPosition(elmInputBox[0], startEndIndex);
        }

        //Gets the actual value of the text area without white spaces from the beginning and end of the value
        function getInputBoxValue() {
            //return $.trim(elmInputBox.val());
            //return $.trim(EditFieldValue);            
            //var html = $.trim($(domInput).html());
            var value =  $.trim($(domInput).val());

//             if(!value)
//               return html;
            return value;
        }

        // This is taken straight from live (as of Sep 2012) GitHub code. The
        // technique is known around the web. Just google it. Github's is quite
        // succint though. NOTE: relies on selectionEnd, which as far as IE is concerned,
        // it'll only work on 9+. Good news is nothing will happen if the browser
        // doesn't support it.
        function textareaSelectionPosition($el) {
            var a, b, c, d, e, f, g, h, i, j, k;
            if (!(i = $el[0])) return;
            if (!$(i).is("textarea")) return;
            if (i.selectionEnd == null) return;
            g = {
                position: "absolute",
                overflow: "auto",
                whiteSpace: "pre-wrap",
                wordWrap: "break-word",
                boxSizing: "content-box",
                top: 0,
                left: -9999
              }, h = ["boxSizing", "fontFamily", "fontSize", "fontStyle", "fontVariant", "fontWeight", "height", "letterSpacing", "lineHeight", "paddingBottom", "paddingLeft", "paddingRight", "paddingTop", "textDecoration", "textIndent", "textTransform", "width", "word-spacing"];
            for (j = 0, k = h.length; j < k; j++) e = h[j], g[e] = $(i).css(e);
            return c = document.createElement("div"), $(c).css(g), $(i).after(c), b = document.createTextNode(i.value.substring(0, i.selectionEnd)), a = document.createTextNode(i.value.substring(i.selectionEnd)), d = document.createElement("span"), d.innerHTML = "&nbsp;", c.appendChild(b), c.appendChild(d), c.appendChild(a), c.scrollTop = i.scrollTop, f = $(d).position(), $(c).remove(), f
        }

        //same as above function but return offset instead of position
        function textareaSelectionOffset($el) {
            var a, b, c, d, e, f, g, h, i, j, k;
            if (!(i = $el[0])) return;
            if (!$(i).is("textarea")) return;
            if (i.selectionEnd == null) return;
            g = {
                position: "absolute",
                overflow: "auto",
                whiteSpace: "pre-wrap",
                wordWrap: "break-word",
                boxSizing: "content-box",
                top: 0,
                left: -9999
            }, h = ["boxSizing", "fontFamily", "fontSize", "fontStyle", "fontVariant", "fontWeight", "height", "letterSpacing", "lineHeight", "paddingBottom", "paddingLeft", "paddingRight", "paddingTop", "textDecoration", "textIndent", "textTransform", "width", "word-spacing"];
            for (j = 0, k = h.length; j < k; j++) e = h[j], g[e] = $(i).css(e);
            return c = document.createElement("div"), $(c).css(g), $(i).after(c), b = document.createTextNode(i.value.substring(0, i.selectionEnd)), a = document.createTextNode(i.value.substring(i.selectionEnd)), d = document.createElement("span"), d.innerHTML = "&nbsp;", c.appendChild(b), c.appendChild(d), c.appendChild(a), c.scrollTop = i.scrollTop, f = $(d).offset(), $(c).remove(), f
        }

        //Scrolls back to the input after autocomplete if the window has scrolled past the input
        function scrollToInput() {
            var elmDistanceFromTop = $(elmInputBox).offset().top; //input offset
            var bodyDistanceFromTop = $('body').offset().top; //body offset
            var distanceScrolled = $(window).scrollTop(); //distance scrolled

            if (distanceScrolled > elmDistanceFromTop) {
                //subtracts body distance to handle fixed headers
                $(window).scrollTop(elmDistanceFromTop - bodyDistanceFromTop);
              }
        }

        //Takes the click event when the user select a item of the dropdown
        function onAutoCompleteItemClick(e) {
            var elmTarget = $(this); //Get the item selected
            var mention = autocompleteItemCollection[elmTarget.attr('data-uid')]; //Obtains the mention

            addMention(mention);
            scrollToInput();
            return false;
        }

        //Takes the click event on text area
        function onInputBoxClick(e) {
            resetBuffer();
        }

        //Takes the blur event on text area
        function onInputBoxBlur(e) {
            hideAutoComplete();
        }

        //Takes the input event when users write or delete something
        function onInputBoxInput(e) {
            updateValues();
            updateMentionsCollection();

            var triggerCharIndex = _.lastIndexOf(inputBuffer, settings.triggerChar); //Returns the last match of the triggerChar in the inputBuffer
            if (triggerCharIndex > -1) { //If the triggerChar is present in the inputBuffer array
                currentDataQuery = inputBuffer.slice(triggerCharIndex + 1).join(''); //Gets the currentDataQuery
                currentDataQuery = utils.rtrim(currentDataQuery); //Deletes the whitespaces
                _.defer(_.bind(doSearch, this, currentDataQuery)); //Invoking the function doSearch ( Bind the function to this)
            }
        }

        //Takes the keypress event
        function onInputBoxKeyPress(e) {
            if(e.keyCode !== KEY.BACKSPACE) { //If the key pressed is not the backspace
                var typedValue = String.fromCharCode(e.which || e.keyCode); //Takes the string that represent this CharCode
                inputBuffer.push(typedValue); //Push the value pressed into inputBuffer
            }
            
//             //Background work
//             if(e.which == 13) {
//               nbr++;
//               if(nbr > 4) { console.log('bada');
//                 sesJqueryObject('#feedbgid_isphoto').val(0);
//                 sesJqueryObject('.sesact_post_box').css('background-image', 'none');
//                 sesJqueryObject('#activity-form').removeClass('feed_background_image');
//               }
//               console.log(nbr); console.log(sesJqueryObject('#feedbgid_isphoto').val()); 
//             }
        }

	    //Takes the keydown event
        function onInputBoxKeyDown(e) {
          
            // This also matches HOME/END on OSX which is CMD+LEFT, CMD+RIGHT
            if (e.keyCode === KEY.LEFT || e.keyCode === KEY.RIGHT || e.keyCode === KEY.HOME || e.keyCode === KEY.END) {
                // Defer execution to ensure carat pos has changed after HOME/END keys then call the resetBuffer function
                _.defer(resetBuffer);

                // IE9 doesn't fire the oninput event when backspace or delete is pressed. This causes the highlighting
                // to stay on the screen whenever backspace is pressed after a highlighed word. This is simply a hack
                // to force updateValues() to fire when backspace/delete is pressed in IE9.
                if (navigator.userAgent.indexOf("MSIE 9") > -1) {
                  _.defer(updateValues); //Call the updateValues function
                }

                return;
            }

            //If the key pressed was the backspace
            if (e.keyCode === KEY.BACKSPACE) {
              //Background work
//               if(nbr > 0) 
//                 nbr--;
                inputBuffer = inputBuffer.slice(0, -1 + inputBuffer.length); // Can't use splice, not available in IE
                return;
            }

            //If the elmAutocompleteList is hidden
            if (!elmAutocompleteList.is(':visible')) {
                return true;
            }

            switch (e.keyCode) {
                case KEY.UP: //If the key pressed was UP or DOWN
                case KEY.DOWN:
                    var elmCurrentAutoCompleteItem = null;
                    if (e.keyCode === KEY.DOWN) { //If the key pressed was DOWN
                        if (elmActiveAutoCompleteItem && elmActiveAutoCompleteItem.length) { //If elmActiveAutoCompleteItem exits
                            elmCurrentAutoCompleteItem = elmActiveAutoCompleteItem.next(); //Gets the next li element in the list
                        } else {
                            elmCurrentAutoCompleteItem = elmAutocompleteList.find('li').first(); //Gets the first li element found
                        }
                    } else {
                        elmCurrentAutoCompleteItem = $(elmActiveAutoCompleteItem).prev(); //The key pressed was UP and gets the previous li element
                    }
                    if (elmCurrentAutoCompleteItem.length) {
                        selectAutoCompleteItem(elmCurrentAutoCompleteItem);
                    }
                    return false;
                case KEY.RETURN: //If the key pressed was RETURN or TAB
                case KEY.TAB:
                    if (elmActiveAutoCompleteItem && elmActiveAutoCompleteItem.length) { //If the elmActiveAutoCompleteItem exists
                        elmActiveAutoCompleteItem.trigger('mousedown'); //Calls the mousedown event
                        return false;
                    }
                break;
            }

            return true;
        }

        //Hides the autoomplete
        function hideAutoComplete() {
            elmActiveAutoCompleteItem = null;
            elmAutocompleteList.empty().hide();
        }

        //Selects the item in the autocomplete list
        function selectAutoCompleteItem(elmItem) {
            elmItem.addClass(settings.classes.autoCompleteItemActive); //Add the class active to item
            elmItem.siblings().removeClass(settings.classes.autoCompleteItemActive); //Gets all li elements in autocomplete list and remove the class active

            elmActiveAutoCompleteItem = elmItem; //Sets the item to elmActiveAutoCompleteItem
        }

	    //Populates dropdown
        function populateDropdown(query, results) {
            elmAutocompleteList.show(); //Shows the autocomplete list

            if(!settings.allowRepeat) {
                // Filter items that has already been mentioned
                var mentionValues = _.pluck(mentionsCollection, 'value');
                results = _.reject(results, function (item) {
                    return _.include(mentionValues, item.name);
                });
            }

            if (!results.length) { //If there are not elements hide the autocomplete list
                hideAutoComplete();
                return;
            }

            elmAutocompleteList.empty(); //Remove all li elements in autocomplete list
            var elmDropDownList = $("<ul>").appendTo(elmAutocompleteList).hide(); //Inserts a ul element to autocomplete div and hide it

            _.each(results, function (item, index) {
                var itemUid = _.uniqueId('mention_'); //Gets the item with unique id

                autocompleteItemCollection[itemUid] = _.extend({}, item, {value: item.name}); //Inserts the new item to autocompleteItemCollection

                var elmListItem = $(settings.templates.autocompleteListItem({
                    'id'      : utils.htmlEncode(item.id),
                    'display' : utils.htmlEncode(item.name),
                    'type'    : utils.htmlEncode(item.type),
                    'content' : utils.highlightTerm(utils.htmlEncode((item.display ? item.display : item.name)), query)
                })).attr('data-uid', itemUid); //Inserts the new item to list

                //If the index is 0
                if (index === 0) {
                    selectAutoCompleteItem(elmListItem);
                }

                //If show avatars is true
                if (settings.showAvatars) {
                    var elmIcon;

                    //If the item has an avatar
                    if (item.avatar) {
                        elmIcon = $(settings.templates.autocompleteListItemAvatar({ avatar : item.avatar }));
                    } else { //If not then we set an default icon
                        elmIcon = $(settings.templates.autocompleteListItemIcon({ icon : item.icon }));
                    }
                    elmIcon.prependTo(elmListItem); //Inserts the elmIcon to elmListItem
                }
                elmListItem = elmListItem.appendTo(elmDropDownList); //Insets the elmListItem to elmDropDownList
            });

            elmAutocompleteList.show(); //Shows the elmAutocompleteList div
	        if (settings.onCaret) {
		        positionAutocomplete(elmAutocompleteList, elmInputBox);
            }
	        elmDropDownList.show(); //Shows the elmDropDownList
        }

        //Search into data list passed as parameter
        function doSearch(query) {
            //If the query is not null, undefined, empty and has the minimum chars
            if (query && query.length && query.length >= settings.minChars) {
                //Call the onDataRequest function and then call the populateDropDrown
                settings.onDataRequest.call(this, 'search', query, function (responseData) {
                    populateDropdown(query, responseData);
                });
            } else { //If the query is null, undefined, empty or has not the minimun chars
                hideAutoComplete(); //Hide the autocompletelist
            }
        }
    
	    function positionAutocomplete(elmAutocompleteList, elmInputBox) {
            var elmAutocompleteListPosition = elmAutocompleteList.css('position');
            if (elmAutocompleteListPosition == 'absolute') {
                var position = textareaSelectionPosition(elmInputBox),
                    lineHeight = parseInt(elmInputBox.css('line-height'), 10) || 18;
                elmAutocompleteList.css('width', '15em'); // Sort of a guess
                elmAutocompleteList.css('left', position.left);
                elmAutocompleteList.css('top', lineHeight + position.top);

                //check if the right position of auto complete is larger than the right position of the input
                //if yes, reset the left of auto complete list to make it fit the input
                var elmInputBoxRight = elmInputBox.offset().left + elmInputBox.width(),
                    elmAutocompleteListRight = elmAutocompleteList.offset().left + elmAutocompleteList.width();
                if (elmInputBoxRight <= elmAutocompleteListRight) {
                    elmAutocompleteList.css('left', Math.abs(elmAutocompleteList.position().left - (elmAutocompleteListRight - elmInputBoxRight)));
                }
            }
            else if (elmAutocompleteListPosition == 'fixed') {
                var offset = textareaSelectionOffset(elmInputBox),
                    lineHeight = parseInt(elmInputBox.css('line-height'), 10) || 18;
                elmAutocompleteList.css('width', '15em'); // Sort of a guess
                elmAutocompleteList.css('left', offset.left + 10000);
                elmAutocompleteList.css('top', lineHeight + offset.top);
            }
        }

        //Resets the text area
        function resetInput(currentVal) {
          if(EditFieldValue)
            currentVal = EditFieldValue;
            mentionsCollection = [];
            //var mentionText = utils.htmlEncode(currentVal);
            var mentionText = currentVal;
            var regex = new RegExp("(" + settings.triggerChar + ")\\[(.*?)\\]\\((.*?):(.*?)\\)", "gi");
            var match, newMentionText = mentionText;
            while ((match = regex.exec(mentionText)) != null) {
                newMentionText = newMentionText.replace(match[0], match[1] + match[2]);
                mentionsCollection.push({ 'id': match[4], 'type': match[3], 'value': match[2], 'trigger': match[1] });
            }
            
            elmInputBox.val(newMentionText);
            updateValues();
        }
        // Public methods
        return {
            //Initializes the mentionsInput component on a specific element.
	        init : function (domTarget) {

                domInput = domTarget;

                initTextarea();
                initAutocomplete();
                initMentionsOverlay();
                resetInput(settings.defaultValue);

                //If the autocomplete list has prefill mentions
                if( settings.prefillMention ) {
                    addMention( settings.prefillMention );
                }
            },

	        //An async method which accepts a callback function and returns a value of the input field (including markup) as a first parameter of this function. This is the value you want to send to your server.
            val : function (callback) {
                if (!_.isFunction(callback)) {
                    return;
                }
                callback.call(this, mentionsCollection.length ? elmInputBox.data('messageText') : getInputBoxValue());
            },

        	//Resets the text area value and clears all mentions
            reset : function () {
                resetInput();
            },

            //Reinit with the text area value if it was changed programmatically
            reinit : function () {
                resetInput(false);
            },

	        //An async method which accepts a callback function and returns a collection of mentions as hash objects as a first parameter.
            getMentions : function (callback) {
                if (!_.isFunction(callback)) {
                    return;
                }
                callback.call(this, mentionsCollection);
            },
            update: function() {
            var messageText = getInputBoxValue();
            // Strip codes
            // add each mention to mentionsCollection
            // And update

           // var mentionText = utils.htmlEncode(getInputBoxValue());
           var mentionText = getInputBoxValue();
            var newMentionText = mentionText;
            if(typeof mentionsCollectionValEdit != 'undefined'){
                   for (i=0;i<mentionsCollectionValEdit.length;i++) {    // Find all matches in a string
                      newMentionText = newMentionText.replace('@_user_'+mentionsCollectionValEdit[i]['id'],mentionsCollectionValEdit[i]['name']);
                      var mention = {
                          'id': mentionsCollectionValEdit[i]['id'],
                          'type': 'user',
                          'name': mentionsCollectionValEdit[i]['name'],
                          'avatar' : mentionsCollectionValEdit[i]['avatar'],
                          'value' : mentionsCollectionValEdit[i]['name'],
                         };
                      mentionsCollection.push(mention);
                      var id = $(domInput).attr('id');
                      if(id){
                        if(typeof mentiondataarray["mention_data_"+id]  == 'undefined'){
                          mentiondataarray["mention_data_"+id] = mentionsCollection;
                        }else{
                          mentiondataarray["mention_data_"+id].push(mention);  
                        }
                      }
                  }
                  elmInputBox.val(newMentionText);
                  updateValues();
            }
          },
        };
    };

    //Main function to include into jQuery and initialize the plugin
    $.fn.mentionsInput = function (method, settings) {

        var outerArguments = arguments; //Gets the arguments
        //If method is not a function
        if (typeof method === 'object' || !method) {
            settings = method;
        }

        return this.each(function () {
            var instance = $.data(this, 'mentionsInput') || $.data(this, 'mentionsInput', new MentionsInput(settings));

            if (_.isFunction(instance[method])) {
                return instance[method].apply(this, Array.prototype.slice.call(outerArguments, 1));
            } else if (typeof method === 'object' || !method) {
                return instance.init.call(this, this);
            } else {
                $.error('Method ' + method + ' does not exist');
            }
        });
    };

})(sesJqueryObject, _);