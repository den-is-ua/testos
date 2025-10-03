<script setup lang="ts">
import { Progress } from "@/components/ui/progress";
import Card from "@/components/ui/card/Card.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import Dialog from "@/components/ui/dialog/Dialog.vue";
import DialogTrigger from "@/components/ui/dialog/DialogTrigger.vue";
import Button from "@/components/ui/button/Button.vue";
import DialogContent from "@/components/ui/dialog/DialogContent.vue";
import DialogHeader from "@/components/ui/dialog/DialogHeader.vue";
import DialogTitle from "@/components/ui/dialog/DialogTitle.vue";
import DialogDescription from "@/components/ui/dialog/DialogDescription.vue";
import Icon from "@/components/Icon.vue";
import ProductsTable from "@/components/main/ProductsTable.vue";
import ImportFileForm from "@/components/main/ImportFileForm.vue";
import { onMounted, ref } from "vue";
import Toaster from "@/components/main/Toaster.vue";
import ImportProgress from "@/components/main/ImportProgress.vue";

const formModalOpened = ref<boolean>(false)

</script>

<template>
    <div class="flex">
        <div class="w-[60%] m-20">
            <ProductsTable/>
        </div>

        <div class="w-[40%] m-20">
            <Dialog v-model:open="formModalOpened">
                <DialogTrigger as-child>
                    <Button variant="outline" class="mb-[20px]" @click="formModalOpened = true">
                        <Icon name="file-down" class="w-4 h-4" />
                        Import File
                    </Button>
                </DialogTrigger>

                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Import file</DialogTitle>
                        <DialogDescription>
                            Choose a CSV click Upload.
                        </DialogDescription>
                    </DialogHeader>

                    <ImportFileForm @form:sent-with-succcess="formModalOpened = false"/>

                </DialogContent>
            </Dialog>
            <div class="w-[100%]">
                <ImportProgress></ImportProgress>
            </div>
        </div>
    </div>
    <Toaster></Toaster>
</template>
