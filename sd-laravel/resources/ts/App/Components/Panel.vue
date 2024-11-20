<script setup lang="ts">
import { ref } from 'vue';


export class FileManager {
    id: number = 1;
    file_url: string = '';
    constructor() {
    }
    public addNewInstance() {
      let newInst =  new FileManager();
        newInst.id = this.id++
    }

    public launch(){
        console.log('Launching file manager instance', this.id);
        const response = fetch('https://jsonplaceholder.typicode.com/todos/1')
    }
}

const fileManager  = new FileManager();
const form = ref<FileManager>(fileManager);


</script>


<template>

    <div>
        <h1>File Manager Panel   {{form.id}} </h1>
        <div> <input type="text" :value="form.file_url"/> </div>
        <button @click="form.launch">Start Call</button>
       <!-- // <div v-for="(fm, index) in fileManagers" :key="fm.id"> -->

        <!-- //<FileManager :id="fm.id" /> -->
    </div>
</template>
