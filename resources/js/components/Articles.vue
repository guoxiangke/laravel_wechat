<template>
  <div>
    <h2>Articles</h2>
    <div v-for="article in articles" v-bind:key="article.id" class="card card-body mb-2">
      <h3>{{articl.title}}</h3>
      <p>{{articl.body}}</p>
    </div>
  </div>
</template>

<script>
  export default{
    data (){
      return {
        articls: [],
        article: {
          id: '',
          title: '',
          body: ''
        },
        article_id: '',
        pagination: {},
        edit: false
      }
    },

    created() {
      this.fetchArticles()
    },

    methods: {
      fetchArticles(){
        fetch('api/posts')
          .then(res => res.json())
          .then(data => {
            console.log(data);
          })
      }
    }
  }
</script>
