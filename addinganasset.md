
# Introduction #
All that is needed to add a Skillsoft Asset to a course is the Skillsoft Unique Asset ID

# Steps #

  1. Add a new "Skillsoft Asset" activity to the course
  1. On the edit form enter the "asset id"<br> <a href='http://sites.google.com/site/moodleskillsoftactivity/images/Capture1.PNG'>http://sites.google.com/site/moodleskillsoftactivity/images/Capture1.PNG</a>
<ol><li>Click "Retrieve Metadata", this will open a new browser window and query the OLSA server. Once the metadata is receieved this window will automatically close.<br> <a href='http://sites.google.com/site/moodleskillsoftactivity/images/Capture2.PNG'>http://sites.google.com/site/moodleskillsoftactivity/images/Capture2.PNG</a>
</li><li>Edit the metadata if you wish<br>
</li><li>Save the form</li></ol>

<h1>Where do I get Asset IDs from?</h1>
It is possible to add most Skillsoft asset types including:<br>
<br>
<ul><li>Courses, example ids: COMM0606, en_US_41527_ng<br>
</li><li>Books24x7 Assets including LDC videos, example ids: 18279<br>
</li><li>Skillsoft Knowledge Center, example id: kc_vb_a02_kc_enus<br>
</li><li>Skillsoft Learning Programme, example id: lp_mh0001<br>
</li><li>SkillBrief/JobAid, example id: COMM0606_jco0606a</li></ul>


Help on retrieving these asset ids is below:<br>
<br>
<ul><li><a href='RetrieveCatalogueAssetId.md'>Courses</a>
</li><li><a href='RetrieveBookId.md'>Books</a>
</li><li><a href='RetrieveCatalogueAssetId.md'>KnowledgeCenters</a>
</li><li><a href='RetrieveCatalogueAssetId.md'>Learning Programmes</a></li></ul>

There are also special ids that can be used:<br>
<br>
<ul><li><code>_addon_books_001</code> - This id if Books24x7 is available will log the user into the Books24x7 home page. This can be used as well as the direct links to individual <a href='RetrieveBookId.md'>Books</a></li></ul>

<ul><li><code>_addon_snl_001</code> - This id will log the user into a "captive" interface on the Skillport platform from where they can use the Skillport Search & Learn interface to find and launch assets. <i>NOTE: This should only be used when using <a href='configuration#Skillsoft_Tracking_Mode.md'>Track to OLSA</a> mode</li></ul></i>

<ul><li><code>sso</code> - This id will log the user into the Skillport platform from where they can use all Skillport functions. NOTE: This is only supported when using <a href='configuration#Skillsoft_Tracking_Mode.md'>Track to OLSA</a> mode