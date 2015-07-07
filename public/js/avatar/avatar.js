/*
* http://css-tricks.com/examples/DragAvatar/
*/
// Required for drag and drop file access
jQuery.event.props.push('dataTransfer');

// IIFE to prevent globals
(function() {

  var s;
  var Avatar = {

    settings: {
      bod: $("body"),
      img: $("#profile-avatar"),
      fileInput: $("#avatar")
    },

    init: function() {
      s = Avatar.settings;
      Avatar.bindUIActions();
    },

    bindUIActions: function() {

      var timer;

      s.bod.on("dragover", function(event) {
        clearTimeout(timer);
        if (event.currentTarget == s.bod[0]) {
          Avatar.showDroppableArea();
        }

        // Required for drop to work
        return false;
      });

      s.bod.on('dragleave', function(event) {
        if (event.currentTarget == s.bod[0]) {
          // Flicker protection
          timer = setTimeout(function() {
            Avatar.hideDroppableArea();
          }, 200);
        }
      });

      s.bod.on('drop', function(event) {
        // Or else the browser will open the file
        event.preventDefault();

        Avatar.handleDrop(event.dataTransfer.files);
      });

      s.fileInput.on('change', function(event) {
        Avatar.handleDrop(event.target.files);
      });
    },

    showDroppableArea: function() {
      s.bod.addClass("droppable");
    },

    hideDroppableArea: function() {
      s.bod.removeClass("droppable");
    },

    handleDrop: function(files) {

      Avatar.hideDroppableArea();

      // Multiple files can be dropped. Lets only deal with the "first" one.
      var file = files[0];

      if (typeof file != 'undefined' && file.type.match('image.*')) {

        Avatar.resizeImage(file, 256, function(data) {
          Avatar.placeImage(data);
        });

      } else {

        //alert("That file wasn't an image.");

      }

    },

    resizeImage: function(file, size, callback) {

      var fileTracker = new FileReader;
      fileTracker.onload = function() {
        Resample(
         this.result,
         size,
         size,
         callback
       );
      };
      fileTracker.readAsDataURL(file);

      fileTracker.onabort = function() {
        alert("The upload was aborted.");
      };
      fileTracker.onerror = function() {
        alert("An error occured while reading the file.");
      }

    },

    placeImage: function(data) {
      s.img.attr("src", data);
    }

  };

  Avatar.init();





    //edit
    var e;
    var AvatarEdit = {

        settings: {
            bod: $("body"),
            img: $("#profile-avatar_edit"),
            fileInput: $("#avatar_edit")
        },

        init: function() {
            e = AvatarEdit.settings;
            AvatarEdit.bindUIActions();
        },

        bindUIActions: function() {

            var timer;

            e.bod.on("dragover", function(event) {
                clearTimeout(timer);
                if (event.currentTarget == e.bod[0]) {
                    AvatarEdit.showDroppableArea();
                }

                // Required for drop to work
                return false;
            });

            e.bod.on('dragleave', function(event) {
                if (event.currentTarget == e.bod[0]) {
                    // Flicker protection
                    timer = setTimeout(function() {
                        AvatarEdit.hideDroppableArea();
                    }, 200);
                }
            });

            e.bod.on('drop', function(event) {
                // Or else the browser will open the file
                event.preventDefault();

                AvatarEdit.handleDrop(event.dataTransfer.files);
            });

            e.fileInput.on('change', function(event) {
                AvatarEdit.handleDrop(event.target.files);
            });
        },

        showDroppableArea: function() {
            e.bod.addClass("droppable");
        },

        hideDroppableArea: function() {
            e.bod.removeClass("droppable");
        },

        handleDrop: function(files) {

            AvatarEdit.hideDroppableArea();

            // Multiple files can be dropped. Lets only deal with the "first" one.
            var file = files[0];

            if (typeof file != 'undefined' && file.type.match('image.*')) {

                AvatarEdit.resizeImage(file, 256, function(data) {
                    AvatarEdit.placeImage(data);
                });

            } else {

                //alert("That file wasn't an image.");

            }

        },

        resizeImage: function(file, size, callback) {

            var fileTracker = new FileReader;
            fileTracker.onload = function() {
                Resample(
                    this.result,
                    size,
                    size,
                    callback
                );
            };
            fileTracker.readAsDataURL(file);

            fileTracker.onabort = function() {
                alert("The upload was aborted.");
            };
            fileTracker.onerror = function() {
                alert("An error occured while reading the file.");
            }

        },

        placeImage: function(data) {
            e.img.attr("src", data);
        }

    };

    AvatarEdit.init();

})();