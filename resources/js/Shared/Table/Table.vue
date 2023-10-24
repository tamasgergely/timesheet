<template>
    <table class="min-w-full divide-y divide-gray-300 shadow mt-5">
        <thead class="bg-gray-50">
            <tr>
                <slot name="head" />
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 bg-white">
            <slot />
        </tbody>
    </table>
</template>