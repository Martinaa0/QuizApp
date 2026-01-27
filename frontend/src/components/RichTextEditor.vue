<template>
  <div class="rich-text-editor">
    <Editor
      v-model="content"
      :init="editorConfig"
      @update:modelValue="handleUpdate"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import Editor from '@tinymce/tinymce-vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  height: {
    type: Number,
    default: 300,
  },
  placeholder: {
    type: String,
    default: 'Enter text...',
  },
})

const emit = defineEmits(['update:modelValue'])

const content = ref(props.modelValue)

const editorConfig = {
  height: props.height,
  menubar: false,
  plugins: [
    'advlist',
    'autolink',
    'lists',
    'link',
    'image',
    'charmap',
    'preview',
    'anchor',
    'searchreplace',
    'visualblocks',
    'code',
    'fullscreen',
    'insertdatetime',
    'media',
    'table',
    'code',
    'help',
    'wordcount',
  ],
  toolbar:
    'undo redo | blocks | ' +
    'bold italic forecolor | alignleft aligncenter ' +
    'alignright alignjustify | bullist numlist outdent indent | ' +
    'removeformat | help',
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
  placeholder: props.placeholder,
}

const handleUpdate = (value) => {
  content.value = value
  emit('update:modelValue', value)
}

watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue !== content.value) {
      content.value = newValue
    }
  }
)
</script>

<style scoped>
.rich-text-editor {
  width: 100%;
}
</style>
