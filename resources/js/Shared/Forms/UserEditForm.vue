<script setup>
import { inject, ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Form from '@/Shared/Input/Form.vue';
import InputText from '@/Shared/Input/Text.vue'
import Select from '@/Shared/Input/Select.vue';
import MultiSelect from '@/Shared/Input/MultiSelect.vue';
import axios from 'axios';

const emit = defineEmits(['closeModal']);

const props = defineProps({
    errors: Object,
    user: Object,
    roles: Object,
    teams: {
        type: Object,
        default: []
    }
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role_id: props.user.role_id,
    password: '',
    password_confirmation: '',
    teams: props.user.teams
});

const search = inject('search');
const page = inject('page');

const update = () => {
    form.put(`/users/${props.user.id}?page=${page}&keyword=${search.value}`, {
        onSuccess: () => emit('closeModal'),
        onError: errors => emit('validationError')
    });
};

const uploadAvatarError = ref(null);
let avatar = ref(props.user ? props.user.avatar : null);

const uploadAvatar = async (e) => {
    try {
        const { data } = await axios.post('/users/upload-profile-image', {
            user_id: props.user.id,
            avatar: e.target.files[0]
        }, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        avatar.value = data.avatar;
        uploadAvatarError.value = null;
    } catch (error) {
        uploadAvatarError.value = error.response.data.message;
    }
};

defineExpose({
    update
});

const currentPage = usePage();
const authUser = computed(() => currentPage.props.auth.user);

watch(
    () => props.user,
    (user) => {
        form.name = user.name;
        form.email = user.email;
        form.role_id = user.role_id;
        form.teams = user.teams;
    }
)
</script>

<template>
    {{ props.errors }}
    <Form @submit.prevent="update">
        <div>
            <label class="text-base font-bold text-accent-secondary lg:text-1xl flex flex-col">
                Profile image:
                <span class="text-xs">Jpg, png, max 1mb</span>
            </label>

            <div class="flex justify-center items-center flex-col">
                <label class="w-32 h-32 rounded-full bg-gray-200 flex justify-center items-center text-center bg-cover bg-center cursor-pointer"
                       :style="avatar ? `background-image: url('/storage/images/${avatar}')` : ''"
                       for="image">
                    <span v-show="!avatar">Click to upload</span>
                </label>
                <span class="text-red-600 text-xs mt-2" v-show="uploadAvatarError">{{ uploadAvatarError }}</span>
            </div>

            <input type="file"
                   name="image"
                   id="image"
                   class="hidden upload"
                   @input="uploadAvatar" />
        </div>

        <InputText v-model="form.name"
                   id="name"
                   label="Name"
                   :error="errors.name"
                   autocomplete="off" />

        <InputText v-model="form.email"
                   id="email"
                   label="E-mail"
                   :error="errors.email"
                   autocomplete="off" />

        <Select v-if="authUser.role === 'Admin'"
                v-model="form.role_id"
                :error="errors.role_id"
                label="Role">

            <option value="">Please choose!</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
            </option>
        </Select>

        <MultiSelect v-if="authUser.role === 'Admin'"
                    :options="props.teams"
                     v-model="form.teams"
                     label="Teams" />

        <InputText v-model="form.password"
                   id="password"
                   label="Password"
                   :error="errors.password"
                   type="password"
                   autocomplete="off" />

        <InputText v-model="form.password_confirmation"
                   id="password_confirmation"
                   label="Password again"
                   type="password"
                   :error="errors.password_confirmation"
                   autocomplete="off" />
    </Form>
</template>