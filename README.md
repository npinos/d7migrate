# Migration example for Drupal 8

This is an example on how to build custom migration plugins to migrate Drupal 7 beans into Drupal 8

# Drupal 8 Migration source plugin for Drupal 7 beans #

This plugin looks for custom beans in a D7 database and creates custom blocks on the target D8 site.
Since D7 Beans are fieldable entities this source plugin extends the FieldableEntity class. 
It will also select the last revision of each bean before migrating.

Assumptions:
Block types already exist on the target website.

This example also includes:
- Migration group example
- Migration plugin for files
