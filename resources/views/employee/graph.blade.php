@extends('layouts.backend')
@section('content')

<style>
    html, body {
        margin: 0;
        padding: 0;
        height: 97%;
        overflow: hidden;
    }

    .page-wrapper {
        position: relative;
        height: 100vh;
        overflow: hidden;
    }

    .content.container-fluid {
        position: relative;
        top: 5px; /* match your header height */
        height: calc(100vh - 90px);
        overflow: auto;
    }

    #orgChart {
        width: 100%;
        height: 97%;
        overflow: auto;
        border: 1px solid lightgray;
        position: relative;
        z-index: 1;
        background: white;
    }
</style>

<script src="https://unpkg.com/gojs/release/go.js"></script>

<div class="page-wrapper" style="overflow-x:auto; overflow-y:hidden">
    <div class="content container-fluid">

        <div id="orgChart" style="width:100%; height:600px; border:1px solid lightgray;"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const $ = go.GraphObject.make;

        const myDiagram = $(go.Diagram, "orgChart", {
            initialAutoScale: go.Diagram.Uniform,
            layout: $(go.LayeredDigraphLayout, {
                direction: 90,
                layerSpacing: 50
            }),
            "undoManager.isEnabled": true
        });

        myDiagram.nodeTemplate = $(
            go.Node, "Auto",
            $(go.Shape, "RoundedRectangle", {
                fill: "lightblue",
                strokeWidth: 0
            }),
            $(go.Panel, "Table", {
                    margin: 6
                },
                $(go.TextBlock, {
                        row: 0,
                        column: 0,
                        font: "bold 14px sans-serif"
                    },
                    new go.Binding("text", "name")),
                $(go.TextBlock, {
                        row: 1,
                        column: 0
                    },
                    new go.Binding("text", "title"))
            )
        );

        myDiagram.linkTemplate = $(
            go.Link, {
                routing: go.Link.Orthogonal,
                corner: 5
            },
            $(go.Shape, {
                strokeWidth: 1.5
            })
        );

        // Load Laravel-passed PHP data into JS
        // const nodeData = @json($nodes);
        // const linkData = @json($links);
        const nodeData = @json(array_values($nodes)); // ensure indexed
        const linkData = @json($links);


        myDiagram.model = new go.GraphLinksModel(nodeData, linkData);
    });
</script>
@endsection
