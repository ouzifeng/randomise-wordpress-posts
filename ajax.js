jQuery(document).ready(function($) {
    let totalPosts = parseInt($('#total_posts').val());
    let processedPosts = 0;

    $('#rpd_submit').click(function(e) {
        e.preventDefault();

        function processPost() {
            return new Promise((resolve, reject) => {
                $.ajax({
                    type: 'POST',
                    url: rpd_ajax_object.ajax_url,
                    data: {
                        action: 'randomize_post_date'
                    },
                    success: function(response) {
                        if (response === 'success') {
                            processedPosts++;
                            let postsLeft = totalPosts - processedPosts;
                            $('#posts-left').text(postsLeft);
                            $('#posts-done').text(processedPosts);
                            $('#progress-bar').val(processedPosts);
                            resolve();
                        } else if (response === 'done') {
                            alert('All post dates have already been randomized!');
                            reject();
                        } else {
                            reject();
                        }
                    }
                });
            });
        }

        function initiateProcess() {
            processPost().then(() => {
                if (processedPosts < totalPosts) {
                    setTimeout(initiateProcess, 2000); // Wait for 2 seconds before processing the next post
                }
            }).catch(() => {
                // Handle any errors or rejections here if needed
            });
        }

        initiateProcess(); // Start the process
    });
});
