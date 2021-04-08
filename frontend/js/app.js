$(document).ready(function() {
    var table = $('#motecData').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "tableViewer.php",
        "pageLength": 25,
        "columnDefs": [{
            "targets": 6,
            "data": "filename",
            "render": function(data, type, row, meta) {
                return '<a class="btn btn-info" href="download.php?file=' + row[6] + '" title="Get the ZIP of the ld and ldx files for this Combination">Download</a>';
            }
        }],
        // initComplete: function() {
        //     this.api().columns([0, 1]).every(function() {
        //         var column = this;
        //         var select = $('<select><option value=""></option></select>')
        //             .appendTo($(column.footer()).empty())
        //             .on('change', function() {
        //                 var val = $.fn.dataTable.util.escapeRegex(
        //                     $(this).val()
        //                 );
        //                 column
        //                     .search(val ? '^' + val + '$' : '', true, false)
        //                     .draw();
        //             });
        //         column.data().unique().sort().each(function(d, j) {
        //             select.append('<option value="' + d + '">' + d + '</option>')
        //         });
        //     });
        // }
    });
    // setInterval( function () { table.ajax.reload( refresh, false ); // user paging is not reset on reload
    //                    }, 30000 );

    function refresh() {
        $("#refreshStatus").show(2000).fadeOut(1000);
    }

    $("#refreshStatus").hide(); // Hide all message areas
    $('.progress').hide();
    $("#startSpinner").hide();
    $('#uploadStatus').empty();
    // File upload via Ajax
    $("#uploadForm").on('submit', function(e) {
        e.preventDefault(); // We will take care of upload using jQuery
        $("#startBtn").attr("disabled", true); // Allow Call to Action to upload for the first time
        $('#startBtn').addClass('disabled'); // Allow Call to Action to upload for the first time
        $('.progress').show();
        $('#uploadStatus').empty().show(); // Prepare messages area for either file upload or processing message
        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = Math.round(((evt.loaded / evt.total) * 100));
                        $(".progress-bar").width(percentComplete + '%');
                        $(".progress-bar").html(percentComplete + '%');
                    }
                    if (percentComplete < 100) {
                        $('#uploadStatus').html('<p class="message info">' + 'We are uploading your ZIP file.</p>');
                    } else {
                        $('.progress').hide();
                        $('#uploadStatus').empty().html('<p class="message info">' + 'Upload Complete.  Unpacking, verifying and addition to the dB in the next few moments.</p>');
                        $("#startBtn").val('Processing...');
                        $("#startSpinner").show();
                    }
                }, false);
                return xhr;
            },
            type: 'POST',
            url: 'upload.php',
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            dataType: "json",
            beforeSend: function() {
                $(".progress-bar").width('0%');
            },
            error: function(xhr, status, error) {
                // alert(status);  // for debug purposes only
                // alert(error);  // for debug purposes only
                $('#uploadStatus').html('<p class="message error">File upload failed, please try again.</p>');
            },
            success: function(data) {
                $('#uploadForm')[0].reset();
                switch (data.response) {
                    case 'good':
                        $('#uploadStatus').html('<p class="message success">' + data.message + '</p>');
                        console.log("|" + data.message + "|");
                        break;
                    case 'bad':
                        $('#uploadStatus').html('<p class="message error">Could not use this file - ' + data.message + '</p>');
                        console.log("|" + data.message + "|");
                        break;
                    case 'part':
                        $('#uploadStatus').html('<p class="message info">Issues encountered:<br> - ' + data.message + '</p>');
                        console.log("|" + data.message + "|"); // This is to give the user an opportunity to see the issues we have noted in their ZIP file contents
                        break;
                }
            },
            complete: function() {
                $('.progress').hide();
                $('#uploadStatus').show(); // Make sure we are showing feedback messages
                setInterval(function() {
                    $('#uploadStatus').hide(); // Hide the messages after 20 sec on screen
                }, 20000);
                table.ajax.reload(null, false); // Load the data from the dB
                $("#startBtn").attr("disabled", false); // Allow the upload button to be used
                $('#startBtn').removeClass('disabled'); // Allow the upload button to be used
                $("#startBtn").val('Upload More'); // Use a different Call to Action as the upload has been used at least once already
                $("#startSpinner").hide();
            }
        });
    });

    // File type validation
    $("#fileToUpload").change(function() {
        var allowedTypes = ['application/zip', 'application/x-zip-compressed'];
        var file = this.files[0];
        var fileType = file.type;
        if (!allowedTypes.includes(fileType)) {
            alert('Filetype not supported: ' + fileType);
            $("#fileToUpload").val('');
            return false;
        }
    });
});