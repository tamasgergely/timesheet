import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Nunito Sans", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                menu: {
                    500: "#373b4a",
                    700: "#1a202e",
                    800: "#111523",
                },
            },
            width: {
                100: "25rem",
                104: "26rem",
                108: "27rem",
                112: "28rem",
                116: "29rem",
                120: "30rem",
                128: "32rem",
            },
        },
    },

    plugins: [forms],
};
