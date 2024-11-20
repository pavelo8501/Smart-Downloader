<script setup lang="ts">
import Panel from './Components/Panel.vue';
import { FileManager } from './Components/Panel.vue';
import { ref } from 'vue';

import { defineProps } from 'vue';




const insOptions = ref<Array<FileManager>>([]);






function addNewInstance() {
    let newInst = new FileManager();
    let count = insOptions.value.length + 1;
    newInst.id = count;
    insOptions.value.push(newInst);
}


</script>

<template>
 <div>
    <ul>
        <li v-for="value in insOptions.values" :key="value[0]">{{ value[1] }}

        </li>
    </ul>
 </div>


  </template>

<style scoped>

</style>
