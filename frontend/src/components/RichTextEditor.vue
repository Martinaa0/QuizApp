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

/* Self-hosted TinyMCE — bundla se lokalno umjesto učitavanja s Tiny Cloud CDN-a
   (CDN bez API ključa stavlja editor u read-only mod) */
import 'tinymce/tinymce'
import 'tinymce/models/dom/model.min.js'
import 'tinymce/themes/silver/theme.min.js'
import 'tinymce/icons/default/icons.min.js'
import 'tinymce/skins/ui/oxide/skin.js'
import 'tinymce/skins/ui/oxide/content.js'
import 'tinymce/skins/content/default/content.js'
import 'tinymce/plugins/advlist/plugin.min.js'
import 'tinymce/plugins/autolink/plugin.min.js'
import 'tinymce/plugins/lists/plugin.min.js'
import 'tinymce/plugins/link/plugin.min.js'
import 'tinymce/plugins/image/plugin.min.js'
import 'tinymce/plugins/charmap/plugin.min.js'
import 'tinymce/plugins/preview/plugin.min.js'
import 'tinymce/plugins/anchor/plugin.min.js'
import 'tinymce/plugins/searchreplace/plugin.min.js'
import 'tinymce/plugins/visualblocks/plugin.min.js'
import 'tinymce/plugins/code/plugin.min.js'
import 'tinymce/plugins/fullscreen/plugin.min.js'
import 'tinymce/plugins/insertdatetime/plugin.min.js'
import 'tinymce/plugins/media/plugin.min.js'
import 'tinymce/plugins/table/plugin.min.js'
import 'tinymce/plugins/help/plugin.min.js'
import 'tinymce/plugins/help/js/i18n/keynav/en.js'

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
    default: 'Unesite tekst...',
  },
})

const emit = defineEmits(['update:modelValue'])

const content = ref(props.modelValue)

const editorConfig = {
  license_key: 'gpl',
  promotion: false,
  height: props.height,
  menubar: false,
  branding: false, // Sakrije "Build with tinyMCE" branding
  statusbar: false, // Sakrije status bar (gdje se prikazuje word count)
  elementpath: false, // Sakrije element path
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
