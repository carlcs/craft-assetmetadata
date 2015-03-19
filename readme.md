

Example:

    {% set asset = entry.audioTracks.first() %}
    {% set assetData = craft.assetMetadata.getData(asset) %}

    <figure itemprop="track" itemscope itemtype="http://schema.org/MusicRecording">
        <figcaption>
            <span itemprop="byArtist">{{ assetData.tags.artist }}</span> -
            <span itemprop="name">{{ assetData.tags.title }}</span>
            <span itemprop="duration" content="{{ assetData.playtime_ISO8601 }}">{{ assetData.playtime_seconds }}</span>
        </figcaption>
        <audio controls src="{{ asset.url }}" itemprop="audio"></audio>
    </figure>

Example:

    {% set artist = craft.assetMetadata.getData(myAssetFileModel, 'artist') %}

ISO 8601 Interval String:

    {% set duration = craft.assetMetadata.getData(myAssetFileModel, 'playtime_ISO8601') %}
    {{ duration|date('%I:%s') }}
