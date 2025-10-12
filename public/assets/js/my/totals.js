// fetch total count [mamor, ajir, military]
async function getEmployeesCount(type, route, typeKey, hasType = true) {
    try {
        let response = null;
        if (hasType) {
            response = await axiosObj.get(route, {
                params: {
                    [typeKey]: type,
                }
            });
        } else {
            response = await axiosObj.get(route);
        }

        if (response != null && response.data != null && response.data['data'] != null) {
            let data = response.data['data'];
            $("#total").html(data['all']);
            $("#employees_count").html(data['employees']);
            $("#ajirs_count").html(data['ajirs']);
            $("#militaries_count").html(data['militaries']);
        }
    } catch (error) {
        DisplayMessage(error['response']['data']['message'], false);
    }
}