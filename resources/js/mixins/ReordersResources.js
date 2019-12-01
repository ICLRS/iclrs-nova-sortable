export default {
  data: () => ({
    reorderLoading: false,
  }),
  computed: {
    reorderDisabled() {
      return !!this.$route.query[this.orderByParameter];
    },
    orderByParameter() {
      return this.viaRelationship ? this.viaRelationship + '_order' : this.resourceName + '_order';
    },
  },
  methods: {
    async updateOrder(event) {
      this.reorderLoading = true;

      const newIdx = event.newIndex;
      const oldIdx = event.oldIndex;
      const resourceId = this.resources[newIdx].id.value;

      let nextId = null;
      let prevId = null;

      if (newIdx < oldIdx) {
          // Moving up - Move before next resource
          nextId = this.resources[newIdx+1].id.value;
      } else {
          // Moving down - Move after previous resource
          prevId = this.resources[newIdx-1].id.value;
      }

      console.log([prevId, resourceId, nextId]);

      try {
        await Nova.request().post('/nova-vendor/nova-sortable/sort/update-order', {
          resourceName: this.resourceName,
          resourceId: resourceId,
          prevId: prevId,
          nextId: nextId
        });
        Nova.success('Resources successfully reordered!');
      } catch (e) {
        Nova.error('An error occurred while trying to reorder the resources.');
      }

      this.reorderLoading = false;
    },

    async moveToStart(resource) {
      this.reorderLoading = true;
      try {
        await Nova.request().post('/nova-vendor/nova-sortable/sort/move-to-start', {
          resourceName: this.resourceName,
          resourceId: resource.id.value,
        });
        await this.refreshResourcesList();
        Nova.success('Resource successfully moved to first position!');
      } catch (e) {
        Nova.error('An error occurred while trying to reorder the resource.');
        this.reorderLoading = false;
      }
      this.reorderLoading = false;
    },

    async moveToEnd(resource) {
      this.reorderLoading = true;
      try {
        await Nova.request().post('/nova-vendor/nova-sortable/sort/move-to-end', {
          resourceName: this.resourceName,
          resourceId: resource.id.value,
        });
        await this.refreshResourcesList();
        Nova.success('Resource successfully moved to last position!');
      } catch (e) {
        Nova.error('An error occurred while trying to reorder the resource.');
        this.reorderLoading = false;
      }
      this.reorderLoading = false;
    },

    async refreshResourcesList() {
      // ! Might break with new Laravel Nova versions
      let parent = this.$parent;
      while (parent && !parent.getResources) {
        parent = parent.$parent;
      }
      if (parent && parent.getResources) await parent.getResources();
    },
  },
};
