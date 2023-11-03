<script setup>
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { onMounted, ref } from 'vue';

const props = defineProps({
    items: Array,
    item: Object,
    type: String
});

const isPopupOpen = ref(false);

const show = (items, item) => {
    items.forEach((item) => {
        item.show = false;
    });

    item.show = !item.show;
    isPopupOpen.value = item.show;
};

const destroy = () => {
    Swal.fire({
        title: '<p class="text-2xl">Are you sure you want to delete?</p>',
        html: '<p class="text-sm">' + swalHtmlText + '</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/${props.type}/${props.item.id}`)
        }
    });
};

const popupRef = ref(null);
const toggleRef = ref(null);
let swalHtmlText = '';

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    if (props.type === 'clients') {
        swalHtmlText = 'All related websites, projects and timers will be deleted.';
    } else if (props.type === 'users') {
        swalHtmlText = 'All related clients, websites, projects and timers will be deleted.';
        swalHtmlText += '<br> If the user is a team leader, the teams led by them will also be deleted.';
    } else if (props.type === 'projects') {
        swalHtmlText = 'All related timers will be deleted.';
    } else if (props.type === 'teams') {
        swalHtmlText = 'The team will be permanently deleted. <br> Customers assigned to the team are also removed from the team.';
    }
})

const handleClickOutside = (event) => {
    if (popupRef.value && toggleRef.value && !popupRef.value.contains(event.target) && !toggleRef.value.contains(event.target)) {
        isPopupOpen.value = false;
    }
};
</script>

<template>
    <div>
        <button ref="toggleRef" @click="show(items, item)"
                type="button"
                class="flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                id="menu-button" aria-expanded="true" aria-haspopup="true">
            <span class="sr-only"></span>
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 20 20"
                 fill="currentColor" aria-hidden="true">
                <path
                      d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
            </svg>
        </button>

        <div ref="popupRef" v-show="item.show && isPopupOpen"
             class="origin-top-right absolute right-1.5 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-50"
             role="menu" aria-orientation="vertical"
             aria-labelledby="menu-button"
             tabindex="-1">
            <div role="none">
                <button @click="$emit('clickOnEdit')"
                        class="text-gray-500 font-medium hover:text-gray-900 hover:bg-gray-50 block px-4 py-2 text-sm w-full text-left">
                    Edit
                </button>
            </div>
            <div class="" role="none">
                <button @click="destroy"
                        method="delete"
                        class="text-gray-500 font-medium hover:text-gray-900 hover:bg-gray-50 block px-4 py-2 text-sm w-full text-left"
                        role="menuitem" tabindex="-1">
                    Delete
                </button>
            </div>
        </div>
    </div>
</template>