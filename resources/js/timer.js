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

    async update(type = null){
        let data = {
            id: this.id,
            interval_id: this.interval_id,
            client_id: this.client_id,
            project_id: this.project_id,
            description: this.description
        };

        if (type === 'updateTime'){
            data.time = Date.now() / 1000;
        }

        try {
            const response = await axios.patch(`/timer/${this.id}`, data);

            return response;

        } catch (error) {
            throw error;
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

            return response;
            
        } catch (error) {
            throw error;
        }
    }

    async destroy() {
        if (this.id){
            try {
                await axios.delete(`/timer/${this.id}`);
            } catch (error) {
                console.log(error);
            }   
        }
    }
}
