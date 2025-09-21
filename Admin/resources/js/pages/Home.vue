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
import DialogFooter from "@/components/ui/dialog/DialogFooter.vue";
import Icon from "@/components/Icon.vue";
import ProductsTable from "@/components/main/ProductsTable.vue";
import ImportFileForm from "@/components/main/ImportFileForm.vue";
import { ref } from "vue";

const progress = ref(32)
</script>

<template>
    <div class="flex">
        <div class="w-[60%] m-20">
            <ProductsTable/>
        </div>

        <div class="w-[40%] m-20">
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="outline" class="mb-[20px]">
                        <Icon name="file-down" class="w-4 h-4" />
                        Import File
                    </Button>
                </DialogTrigger>

                <DialogContent class="sm:max-w-[425px]">
                    <DialogHeader>
                        <DialogTitle>Import file</DialogTitle>
                        <DialogDescription>
                            Choose a CSV or Excel file and click Upload.
                        </DialogDescription>
                    </DialogHeader>

                    <ImportFileForm />
                    
                </DialogContent>
            </Dialog>
            <Card class="w-[100%]">
                <CardHeader>
                    <CardTitle>Import name</CardTitle>
                </CardHeader>
                <CardContent>
                    <Progress v-model="progress" class="w-3/5" />
                </CardContent>
            </Card>
        </div>
    </div>
</template>
