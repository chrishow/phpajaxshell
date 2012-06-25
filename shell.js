/* 
 *  © Chris How, Primesolid 2011
 *  All rights reserved.
 */

/* JSLint setup */
/*global $, JSTarget, alert, History, console, NONCE */
/* browser white */


(function() {
    "use strict";
    var $body, $input, $output, history = [], historyPosition = 0, currentLine, cwd, prompt = '$ ' ;

    function write(line) {
        var newEl = $('<span>').text(line);
        $output.append(newEl);
        $body.scrollTop($body.height());
    }

    function flash() {
        $('html').addClass('inverse');
        setTimeout(function() {
            $('html').removeClass('inverse');
        }, 50);
    }


    function handleAutocompleteResults(data, originalInput, curInput, stem) {
        console.log(data);
        var i, len, j, currentMatch, datum, matchedAtLeastOneChar = false, caretPosition;
        if(data.length) {
            if(data.length === 1) {
                caretPosition = (originalInput.substr(0, $input[0].selectionStart - stem.length) + data[0]).length;
                $input.val(originalInput.substr(0, $input[0].selectionStart - stem.length) + data[0] + originalInput.substr($input[0].selectionStart, originalInput.length));
                // set caret position
                $input[0].selectionStart = $input[0].selectionEnd = caretPosition;
            
            } else {
                // mulitple possibilites. 
                for(i = 0, len = data.length; i < len; i += 1) {
                    datum = data[i];
                    if(currentMatch) {
                        for(j = 0; j < currentMatch.length; j += 1) {
                            if(currentMatch.charAt(j) !== datum.charAt(j)) {
                                currentMatch = currentMatch.substr(0, j);
                                break;
                            } else if(j > 1) {
                                matchedAtLeastOneChar = true;
                            }
                        }
                    } else {
                        currentMatch = datum;
                    }
                }
                if(matchedAtLeastOneChar) {
                    $input.val(originalInput.substr(0, $input[0].selectionStart - stem.length) + currentMatch + originalInput.substr($input[0].selectionStart, originalInput.length));                
                }
                write($input.val() + "\n");
                write(data.join(' ') + "\n");
                flash();
            }
        } else {
            flash();
        }
    
    }


    function autoComplete() {
        var type;
        var originalInput = $input.val();
        var curInput = originalInput.substr(3).replace(/^\s+/,'');
        var stem = originalInput.substr(0, $input[0].selectionStart);
        if(curInput.indexOf(' ') === -1) {
            // complete command
            stem = stem.substr(stem.lastIndexOf(' ')+1);
            type = 'command';
        } else {
            // complete filename
            stem = stem.substr(stem.lastIndexOf(' ')+1);
            type = 'file';
        }
        console.log('stem: ' + stem);
        $.ajax({
            url: 'slave.php',
            data: {
                action: 'complete',
                type: type,
                stem: stem,
                nonce: NONCE
            },
            success: function(data) {
                handleAutocompleteResults(data, originalInput, curInput, stem);
            }
        });
    
    }



    function logout() {
        window.location = '?action=logout';
    }





    function processLine() {
        var originalLine = $input.val();
        var line = originalLine;
        write(originalLine + "\n");
    
        if(line.indexOf(prompt) === 0) {
            line = line.substr(prompt.length);
        }

        if (line) {
            history.push(originalLine);
            if(line === 'clear') {
                $output.html('');
            } else if (line === 'logout') {
                logout();
                return;            
            //        } else if (line.indexOf('vi ') == 0) {
            //            openEditor(line.substr(('vi ').length));
            //            return;
            }
            $input.hide();
            $.ajax({
                url : 'slave.php',
                data: {
                    cmd: line,
                    nonce: NONCE                
                },
                success: function(data) {
                    $input.show();
                    $input[0].selectionStart = $input.val().length;
                    $body.scrollTop($body.height());
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error: ' + textStatus);
                    $input.show();
                    $input[0].selectionStart = $input.val().length;
                    $body.scrollTop($body.height());
                },
                onreadystatechange : function(myXhr, jqXhr){
                    var chunk, len;
                    if(myXhr.readyState === 3 || myXhr.readyState === 4) {
                        chunk = myXhr.responseText.substr(myXhr.outputCount);
                        len = chunk.length;
                        write(chunk);
                        myXhr.outputCount += len;
                    } 
                }            
            });
        
    
        } 
    
        $input.val(prompt);
    }

    function handleKey(e) {
        if(e.keyCode === 13) {
            processLine();
            return false;
        } else if (e.keyCode === 38) { // up
            if(historyPosition === 0) {
                currentLine = $input.val();
            }
            if(historyPosition < history.length) {
                historyPosition += 1;
                $input.val(history[history.length - historyPosition]);
            }
            return false;
        } else if (e.keyCode === 40) { // down
            if(historyPosition > 1) {
                historyPosition -= 1;
                $input.val(history[history.length - historyPosition]);        
            } else if (historyPosition === 1) {
                historyPosition -= 1;
                $input.val(currentLine);
            }
            return false;
        } else if (e.keyCode === 9) { // tab
            autoComplete();
            return false;
        } else if (e.keyCode === 8) { // delete
            if($input.val().length <= prompt.length) {
                return false;            
            }
        } else if (e.keyCode === 67 && e.ctrlKey) { // ctrl-c
            write($input.val());
            $input.val(prompt);
        } else if (e.keyCode === 76 && e.ctrlKey) { // ctrl-l
            $output.html('');
        }
    
        return true;
    }


    function Editor(file) {
        var $editor = $('#editor');
        $editor.fadeIn('fast');
    
        //    alert(file);
        var $links = $("<div>[ <a href='#' id='editor_cancel'>Cancel</a> ]&nbsp;&nbsp;&nbsp;[ <a href='#' id='editor_save'>Save</a> ]</div>");
        $editor.find('#editor_links').append($links);
        $('#editor_cancel').bind('click', function(){
            $editor.fadeOut('fast');
        });
    }
    
    function openEditor(file) {
        var editor = new Editor(file);
    }
    
    // and off we go...
    $(document).ready(function(){
        $body = $('body');
        $input = $('#input');
        $output = $('#output');
    
        $input.focus();
        $input.val(prompt);
    
        $input.on('keydown', function(e) {
            return handleKey(e);
        }).on('blur', function() {
            });
        
        $('html').on('click', function() {
            $input.focus();
        });
        $body.on('click', function(e) {
            e.stopPropagation();
        });
    
        $.ajaxPrefilter(function( options, junk, jqXHR ) {
            if ( options.onreadystatechange ) {
                var xhrFactory = options.xhr;
                options.xhr = function() {
                    var xhr = xhrFactory.apply( this, arguments );
                    xhr.outputCount = 0;
                    function handler() {
                        options.onreadystatechange( xhr, jqXHR );
                    }
                    if ( xhr.addEventListener ) {
                        xhr.addEventListener( "readystatechange", handler, false );
                    } else {
                        setTimeout( function() {
                            var internal = xhr.onreadystatechange;
                            if ( internal ) {
                                xhr.onreadystatechange = function() {
                                    handler();
                                    internal.apply( this, arguments ); 
                                };
                            }
                        }, 0 );
                    }
                    return xhr;
                };
            }
        });
        
    
    });
    
}());