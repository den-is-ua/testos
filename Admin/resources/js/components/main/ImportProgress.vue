<script setup lang="ts">
import { importsStore } from "@/stores/progressImportStore";
import Card from "../ui/card/Card.vue";
import CardHeader from "../ui/card/CardHeader.vue";
import CardTitle from "../ui/card/CardTitle.vue";
import Progress from "../ui/progress/Progress.vue";
import CardContent from "../ui/card/CardContent.vue";
import Echo from "laravel-echo";
import Pusher from "pusher-js";
import { Import } from "@/types";

const imports = importsStore().getAll();
 
var pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
});

var channel = pusher.subscribe("import-progress");

channel.bind("updated-progress", (data: Import) => {
    importsStore().update(data.id, data)

    if (data.completed) {
        importsStore().remove(data.id)
    }
});

</script>

<template>
    <Card class="w-[100%] mt-[20px]" v-for="imp in imports">
        <CardHeader>
            <CardTitle>{{ imp.file_name }}</CardTitle>
        </CardHeader>
        <CardContent>
            <Progress v-model="imp.progress" class="w-3/5" />
        </CardContent>
    </Card>
</template>