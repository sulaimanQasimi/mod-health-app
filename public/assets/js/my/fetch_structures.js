async function getParentStructures(branchId, elementId, route) {
    if (elementId == null || elementId == "") return false;
    if (branchId != null) {
        if (branchId == 0) {
            $("#" + elementId + " option").each(function () {
                if (this.value != 0) {
                    $(this).remove();
                }
            });
        } else {
            let response = await axiosObj.get(route, {
                params: {
                    type: 'parent',
                    branch: branchId
                }
            });
            let structures = $("#" + elementId);
            structures.empty();
            if (response != null && response.data != null) {
                structures.append(response.data);
            }
        }
    }
}

async function getSubStructures(parentStrucutreId, elementId, route) {
    if (elementId == null || elementId == "") return false;
    if (parentStrucutreId != null) {
        if (parentStrucutreId == 0) {
            $("#" + elementId + " option").each(function () {
                if (this.value != 0) {
                    $(this).remove();
                }
            });
        } else {
            let response = await axiosObj.get(route, {
                params: {
                    type: 'child',
                    pStructure: parentStrucutreId
                }
            });
            let subStructures = $("#" + elementId);
            subStructures.empty();
            if (response != null && response.data != null) {
                subStructures.append(response.data);
            }
        }
    }
}