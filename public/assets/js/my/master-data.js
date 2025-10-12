async function getBloodGroups(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getEthnicities(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getRelationshipTypes(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getJalbJazbieRejectionReasons(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getCountries(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getProvinces(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getProvinces2(route, elementId1, elementId2) {
    if (elementId1 == null || elementId1 == "" || elementId1 == 0 || elementId2 == null || elementId2 == "" || elementId2 == 0) return false;

    let response = await axiosObj.get(route);

    let obj1 = $("#" + elementId1);
    let obj2 = $("#" + elementId2);

    obj1.empty();
    obj2.empty();

    if (response != null && response.data != null) {
        obj1.append(response.data);
        obj2.append(response.data);
    }
}

async function getStudentRejectionReasons(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}


async function getEducationalCenters(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}


async function getReasons(route, elementId, type, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            type: type,
            selected: selected,
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getMoqarariApprovalAuthorities(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getParentStructures(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getAcademyDawraFeraghat(route, elementId, reference, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0 || reference == null || reference == "" || reference == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected,
            reference: reference
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getAcademyDawraShomolyat(route, elementId, reference, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0 || reference == null || reference == "" || reference == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected,
            reference: reference
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getRanks(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getRolesByModule(route, elementId, selectedModule, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected_module: selectedModule,
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getJalbJazbCentersRendered(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}

async function getMilitaryProfessions(route, elementId, selected = null) {
    if (elementId == null || elementId == "" || elementId == 0) return false;

    let response = await axiosObj.get(route, {
        params: {
            selected: selected
        }
    });

    let obj = $("#" + elementId);

    obj.empty();
    if (response != null && response.data != null) {
        obj.append(response.data);
    }
}