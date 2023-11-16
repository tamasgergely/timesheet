const accurateTimer = (fn, time = 1000) => {
    let nextAt, timeout;
    
    nextAt = new Date().getTime() + time;

    const wrapper = () => {
        nextAt += time;
        timeout = setTimeout(wrapper, nextAt - new Date().getTime());
        fn();
    };

    const cancel = () => clearTimeout(timeout);

    timeout = setTimeout(wrapper, nextAt - new Date().getTime());

    return { cancel };
};

var timer;

self.addEventListener("message", function (e) {
    switch (e.data) {
        case "start":
            timer = accurateTimer(() => {
                self.postMessage("tick");
            });
            break;
        case "stop":
            timer.cancel();
            break;
    }
});