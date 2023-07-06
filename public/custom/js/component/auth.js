
$(document).on('submit','#authForm',function (e) {
    e.preventDefault();
    const loginData = serializeObject($(this));
    let postData = {
        email_id: loginData.email_id,
        password: loginData.password,
    };
    (new HttpRequest()).auth(postData).done(function (response) {
        if (response.status === true) {
            toastr.info(response.message, "info", options);
            // window.location.href = response.route_to;

        }
    }).fail(function (response) {
        console.warn(response)
        toastr.error(response.responseJSON.message, "Error",options);
    });
});
