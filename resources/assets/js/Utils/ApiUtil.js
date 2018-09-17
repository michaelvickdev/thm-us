const serialize = (obj, prefix) => {
    const str = [];
    Object.keys(obj).forEach((p) => {
        const k = prefix ? prefix + "[" + p + "]" : p;
        const v = obj[p];
        str.push((v !== null && typeof v === "object") ?
            serialize(v, k) : k + "=" + v);
    });
    return str.join("&");
};

const fetchFromApi = (url, params) => {
    return new Promise((resolve) => {
        fetch(`${url}?${serialize(params)}`, {
            credentials: 'same-origin'
        })
            .then((result) => result.json())
            .then((response) => resolve(response));
    });
};

const putToApi = (url, params) => {
    return new Promise((resolve) => {
        fetch(`${url}?${serialize(params)}`, {
            method: 'put',
            credentials: 'same-origin'
        })
            .then((result) => result.json())
            .then((response) => resolve(response));
    });

};
const postToApi = (url, data) => {
    return new Promise((resolve, reject) => {
        fetch(`${url}`, {
            method: 'post',
            body: data,
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) {
                    reject(response.headers.get('statustext'));
                    throw Error(response.headers.get('statustext'));
                }

                resolve(response.json())
                return response;
            }).catch(function (error) {
            console.log(error);

            reject(error);
        })

    });
};

const deleteToApi = (url, params) => {
    return new Promise((resolve) => {
        fetch(`${url}?${serialize(params)}`, {
            method: 'delete',
            credentials: 'same-origin'
        })
            .then((result) => result.json())
            .then((response) => resolve(response));
    });
};

module.exports = {
    fetchFromApi,
    putToApi,
    postToApi,
    deleteToApi,
};