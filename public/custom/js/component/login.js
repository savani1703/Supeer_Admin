$("#authForm").on("submit", (e) => {
    e.preventDefault();
    let AuthFormData = getFormData($("#authForm"));

    FsClient.post('/authentication',AuthFormData)
        .then(response => {
            toastr.success(response.message,"success",toastOption);
            window.location.href = response.route_to
        })
        .catch(error => {
            toastr.error(error.responseJSON.message,"error",toastOption);
        });
});
