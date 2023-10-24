export default class Interval {
    constructor(obj) {
        this.id = obj.id;
        this.description = obj.description;
        this.hours = obj.hours;
        this.minutes = obj.minutes;
        this.seconds = obj.seconds;
        this.start = obj.start;
        this.stop = obj.stop;

        if (typeof this.counter == "undefined") {
            this.counter = new Worker("/js/counter.js");
        }
    }

}
