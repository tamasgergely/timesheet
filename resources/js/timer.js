import axios from "axios";

export default class Timer {
    constructor(obj) {

        for (const [key, value] of Object.entries(obj)) {
            this[key] = value;
        }
        
        if (typeof this.counter == "undefined") {
            this.counter = new Worker("/js/counter.js");
        }
    }

    count() {
        this.seconds++;

        if (this.seconds > 59) {
            this.seconds = 0;
            this.minutes++;
        }

        if (this.minutes > 59) {
            this.seconds = 0;
            this.minutes = 0;
            this.hours++;
        }
    }

    async updateTimerInterval() {
        try {
            const response = await axios.patch(`/timer/${this.id}`, {
                id: this.id,
                interval_id: this.interval_id,
                description: this.description,
                time: Date.now() / 1000,
            });

            this.id = response.data.timer_id;
            this.interval_id = response.data.interval_id;
        } catch (error) {
            console.log(error);
        }
    }

    async update(page, successMsg){
        try {
            await axios.patch(`/timer/${this.id}`, {
                id: this.id,
                client_id: this.client_id,
                project_id: this.project_id,
                description: this.description
            });

            page.props.flash.success = successMsg;
        } catch (error) {
            page.props.flash.error = true
            console.error(error);
        }
    }

    async save() {
        try {
            const response = await axios.post("/timer", {
                client_id: this.client_id,
                project_id: this.project_id,
                description: this.description,
            });

            this.id = response.data.timer_id;
            this.interval_id = response.data.interval_id;
        } catch (error) {
            console.log(error);
        }
    }

    async destroy() {
        if (this.id){
            try {
                const response = await axios.delete(`/timer/${this.id}`);
            } catch (error) {
                console.log(error);
            }   
        }
    }
}
