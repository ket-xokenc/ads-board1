/*
 * jQuery File Upload Plugin JS Example 8.9.1
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/* global $, window */

$(function () {
    'use strict';

    $('#fileupload').fileupload({
        url: '/profile/add-img',
        autoUpload:true,
        acceptFileTypes:/(\.|\/)(jpe?g|png)$/i ,
        maxFileSize:2000000,
        minFileSize:1,
        maxNumberOfFiles:9,
        previewMinWidth:150,
        previewMinHeight:150,
        previewMaxWidth:150,
        previewMaxHeight:150,
        dropZone: $('#dropzone')
    });

    $('#fileupload')
        .bind('fileuploaddrop', function (e, data) {

        });

        $('#fileupload').addClass('fileupload-processing');
        $.ajax({
            url: $('#fileupload').fileupload('option', 'url'),
            dataType: 'json',
            context: $('#fileupload')[0]
        }).always(function () {
            $(this).removeClass('fileupload-processing');
        }).done(function (result) {
            $(this).fileupload('option', 'done')
                .call(this, $.Event('done'), {result: result});
        });

});

